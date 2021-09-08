<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvertisementManager\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Setup\EavSetup;

class AdvertisementInsertDefaultData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var ControllersRepository
     */
    private $controllersRepository;

    protected $appstate;

    protected $_productType = \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ControllersRepository $controllersRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ControllersRepository $controllersRepository,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Model\Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Webkul\MpAdvertisementManager\Logger\AdsLogger $logger
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->controllersRepository = $controllersRepository;
        $this->_productModel = $productModel;
        $this->_storeManager = $storeManager;
        $this->_productFactory = $productFactory;
        $this->_eavConfig = $eavConfig;
        $this->appState = $appState;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->directoryList = $directoryList;
        $this->_logger = $logger;
        $this->file = $file;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $data = [];
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();
        
        if (!count($this->controllersRepository->getByPath('mpads/block'))) {
            $data[] =
                [
                    'module_name' => 'Webkul_MpAdvertisementManager',
                    'controller_path' => 'mpads/block',
                    'label' => 'Ads Block',
                    'is_child' => '0',
                    'parent_id' => '0',
                ];
        }
        if (!count($this->controllersRepository->getByPath('mpads/advertise'))) {
            $data[] = [
                    'module_name' => 'Webkul_MpAdvertisementManager',
                    'controller_path' => 'mpads/advertise',
                    'label' => 'Advertise',
                    'is_child' => '0',
                    'parent_id' => '0',
                ];
        }

        if (!empty($data)) {
            $connection->insertMultiple($this->moduleDataSetup->getTable('marketplace_controller_list'), $data);
        }
        $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        $product = $this->_productFactory->create();
        $this->registry->register('isSecureArea', true);
        try {
            if ($product->getIdBySku('wk_mp_ads_plan') === false) {
                $product = $this->_productFactory->create();
                $attributeSetId = $this->_productModel->getDefaultAttributeSetId();
                $product->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
                $product->setWebsiteIds([$this->_storeManager->getDefaultStoreView()->getWebsiteId()]);
                $product->setTypeId($this->_productType);
                $product->addData(
                    [
                        'name' => 'Ads Plan',
                        'attribute_set_id' => $attributeSetId,
                        'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
                        'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE,
                        'weight' => 1,
                        'sku' => 'wk_mp_ads_plan',
                        'tax_class_id' => 0,
                        'description' => 'Seller Ads Plan',
                        'short_description' => 'Seller Ads Plan',
                        'stock_data' => [
                            'manage_stock' => 1,
                            'qty' => 999,
                            'is_in_stock' => 1
                        ]
                    ]
                );
                $product->save();
                $productModel= $this->_productFactory->create()->loadByAttribute('sku', 'wk_mp_ads_plan');
                $fileDriver = $this->file;
                $directory_list = $this->directoryList;
                $sampleFilePath = $directory_list->getPath('app').
                "/code/Webkul/MpAdvertisementManager/view/base/web/images/wk-mp-ads-image.png";
                if (!$fileDriver->isExists($sampleFilePath)) {
                    $sp="/vendor/webkul/marketplace-advertisement-manager/src/app/code/Webkul/MpAdvertisementManager/";
                    $sampleFilePath = $directory_list->getRoot().$sp.
                    "view/base/web/images/wk-mp-ads-image.png";
                }
                if (!$fileDriver->isExists($sampleFilePath)) {
                    $this->_logger->info('Advertisment product image path does not exist.--'.$sampleFilePath);
                } else {
                    $mediaPath = $directory_list->getPath('media');
                    $this->copyImageToMediaDirectory($sampleFilePath, $mediaPath);
                    $mediaPath =$mediaPath."/wk-mp-ads-image.png";
                    $mediaUrl = "wk-mp-ads-image.png";
                    if ($fileDriver->isExists($mediaPath)) {
                        $productModel->addImageToMediaGallery($mediaUrl, ['image', 'small_image',
                        'thumbnail'], false, true);
                        $productModel->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_logger->critical("Error While creating product at time of installing module". $e->getMessage());
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public function copyImageToMediaDirectory($sampleFilePath, $mediaPath)
    {
        try {
            $imagePath = $sampleFilePath;
            $newPath = $mediaPath;
            $ext = '.png';
            $newName  = $newPath."/wk-mp-ads-image".$ext;
            $copied = $this->fileop->cp($imagePath, $newName);
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }
    }

   /**
    * {@inheritdoc}
    */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
    
    /**
     * copyImageToMediaDirectory function copy image to media directory
     *
     * @param string $sampleFilePath
     * @param string $mediaPath
     * @return void
     */
}
