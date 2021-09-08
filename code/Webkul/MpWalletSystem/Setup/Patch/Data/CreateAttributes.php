<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Setup\Patch\Data;

use Magento\Framework\Setup;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Patch of Webkul MpWalletSystem CreateAttributes
 */
class CreateAttributes implements DataPatchInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productModel;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var Installer
     */
    protected $productType = \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleReader;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $catalogProductTypeManager;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $reader;

    /**
     * Initialize dependencies
     *
     * @param ModuleDataSetupInterface                             $moduleDataSetup
     * @param \Magento\Catalog\Model\Product                       $productModel
     * @param \Psr\Log\LoggerInterface                             $logger
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\Catalog\Model\ProductFactory                $productFactory
     * @param \Magento\Eav\Model\Config                            $eavConfig
     * @param EavSetupFactory                                      $eavSetupFactory
     * @param \Magento\Framework\App\State                         $appstate
     * @param \Magento\Framework\Module\Dir\Reader                 $moduleReader
     * @param \Magento\Framework\Filesystem                        $filesystem
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $catalogProductTypeManager
     * @param \Magento\Framework\Filesystem\Driver\File            $file
     * @param \Magento\Framework\File\Mime                         $mime
     * @param \Magento\Framework\Filesystem\Driver\File            $fileDriver
     * @param \Magento\Framework\Module\Dir\Reader                 $reader
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Catalog\Model\Product $productModel,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Model\Config $eavConfig,
        EavSetupFactory $eavSetupFactory,
        \Magento\Framework\App\State $appstate,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Model\Product\TypeTransitionManager $catalogProductTypeManager,
        \Magento\Framework\Filesystem\Driver\File $file,
        \Magento\Framework\File\Mime $mime,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Module\Dir\Reader $reader
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->logger = $logger;
        $this->productModel = $productModel;
        $this->storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->appState = $appstate;
        $this->moduleReader = $moduleReader;
        $this->filesystem = $filesystem;
        $this->catalogProductTypeManager = $catalogProductTypeManager;
        $this->fileDriver = $fileDriver;
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->eavConfig->clear();
        $appState = $this->appState;
        $appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        $product = $this->productFactory->create();
        $store = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $attributeSetId = $this->productModel->getDefaultAttributeSetId();

        $mageProduct = $this->productFactory->create();
        $mageProduct->setAttributeSetId($attributeSetId);
        $mageProduct->setTypeId($this->productType);
        $mageProduct->setStoreId($store);

        $productId = $this->productModel->getIdBySku('wk_wallet_amount');

        if (!$productId) {
            $requestData = [
                'product' => [
                    'name' => 'Wallet Amount',
                    'attribute_set_id' => $attributeSetId,
                    'status' => \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED,
                    'visibility' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE,
                    'sku' => 'wk_wallet_amount',
                    'tax_class_id' => 0,
                    'price' => 0,
                    'description' => 'wallet amount',
                    'short_description' => 'wallet amount',
                    'stock_data' => [
                        'use_config_manage_stock' => 0,
                        'manage_stock' => 0,
                        'is_decimal_divided' => 0
                    ],
                    'quantity_and_stock_status' => [
                        'qty' => 1,
                        'is_in_stock' => 1
                    ]
                ]
            ];

            $catalogProduct = $this->productInitialize($mageProduct, $requestData);
            $this->catalogProductTypeManager->processProduct($catalogProduct);
            if ($catalogProduct->getSpecialPrice() == '') {
                $catalogProduct->setSpecialPrice(null);
                $catalogProduct->getResource()->saveAttribute($catalogProduct, 'special_price');
            }
            $catalogProduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)->save();
            $this->moveDirToMediaDir();
            $imagePath = $this->getViewFileUrl().'mpwalletsystemwallet.png';

            $catalogProduct->addImageToMediaGallery($imagePath, ['image', 'small_image', 'thumbnail'], false, false);
            $catalogProduct->save();
        }

        $eavSetup= $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'wallet_credit_based_on',
            [
                'group' => 'Product Details',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Product Wallet Credit Amount Based On', /* Lablel of your attribute*/
                'input' => 'select',
                'class' => '',
                'source' => \Webkul\MpWalletSystem\Model\Config\Source\ProductattrOptions::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'note' => 'Product Credit amount based on rules amount or on wallet credit amount',
                'apply_to' => 'simple,downloadable,virtual,bundle,configurable'
            ]
        );

        $eavSetup= $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'wallet_cash_back');
        $eavSetup->addAttribute(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            'wallet_cash_back',
            [
                'type' => 'decimal',
                'label' => 'Wallet Credit Amount',
                'input' => 'price',
                'required' => false,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => 'Product Details',
                'used_in_product_listing' => true,
                'visible_on_front' => true,
                'visible' => true,
                'user_defined' => true,
                'unique' => false,
                'is_configurable' => false,
                'used_for_promo_rules' => true,
                'backend' => '',
                'default' => 0,
                'frontend' => '',
                'frontend_class'=>'validate-zero-or-greater',
                'label' =>  'Wallet Cash Back',
                'note' => 'Product wise credit amount.',
                'apply_to' => 'simple,downloadable,virtual,bundle,configurable'
            ]
        );
    }

    /**
     * GetViewFileUrl function is used to get file viewl url
     *
     * @return string
     */
    public function getViewFileUrl()
    {
        return $this->filesystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath();
    }

    /**
     * Product Initialize function
     *
     * @param \Magento\Catalog\Model\Product $catalogProduct
     * @param array                          $requestData
     * @return \Magento\Catalog\Model\Product
     */
    private function productInitialize(\Magento\Catalog\Model\Product $catalogProduct, $requestData)
    {
        $requestProductData = $requestData['product'];
        $requestProductData['product_has_weight'] = 0;
        $catalogProduct->addData($requestProductData);
        $websiteIds = [];
        $allWebsites = $this->storeManager->getWebsites();
        foreach ($allWebsites as $website) {
            $websiteIds[] = $website->getId();
        }
        $catalogProduct->setWebsiteIds($websiteIds);
        return $catalogProduct;
    }

     /**
      * MoveDirToMediaDir move directories to media
      *
      * @return void
      */
    private function moveDirToMediaDir()
    {
        $type = \Magento\Framework\App\Filesystem\DirectoryList::MEDIA;
        $smpleFilePath = $this->filesystem->getDirectoryRead($type)
            ->getAbsolutePath().'mpwalletsystem';

        $modulePath = $this->reader->getModuleDir('', 'Webkul_MpWalletSystem');
        $mediaFile = $modulePath.'/view/frontend/web/images/wallet.png';
        try {
            if (!$this->fileDriver->isExists($smpleFilePath)) {
                $this->fileDriver->createDirectory($smpleFilePath, 0777, true);
            }
        } catch (\Exception $e) {
            $this->logger->addInfo($e->getError());
            return false;
        }

        $filePath = $smpleFilePath.'wallet.png';

        try {
            if (!$this->fileDriver->isExists($filePath)) {
                if ($this->fileDriver->isExists($mediaFile)) {
                    $this->fileDriver->copy($mediaFile, $filePath);
                }
            }
        } catch (\Exception $e) {
            $this->logger->addInfo($e->getError());
            return false;
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
}
