<?php
/**
 * Webkul Software
 *
 * @category Magento
 * @package  Webkul_MpPromotionCampaign
 * @author   Webkul
 * @license  https://store.webkul.com/license.html
 */
namespace Webkul\MpPromotionCampaign\Console\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MpPromotionCampaign
 */
class DisablePromotion extends SymfonyCommand
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $_eavAttribute;

    /**
     * @var \Magento\Framework\Module\Status
     */
    protected $_modStatus;

    protected $productCollection;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @param \Magento\Eav\Setup\EavSetupFactory        $eavSetupFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Module\Manager         $moduleManager
     * @param \Magento\Framework\Module\Status          $modStatus
     * @param \Webkul\Marketplace\Helper\Data           $helper
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Webkul\MpPromotionCampaign\Model\CampaignProductFactory $campaignProduct,
        \Magento\Framework\Module\Status $modStatus,
        \Webkul\MpPromotionCampaign\Model\Campaign $campaign,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\App\State $state,
        \Webkul\MpPromotionCampaign\Model\CampaignJoin $sellerCampaign,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MpPromotionCampaign\Helper\Data $helper
    ) {
        $this->sellerCampaign = $sellerCampaign;
        $this->campaign = $campaign;
        $this->product = $product;
        $this->state = $state;
        $this->campaignProduct = $campaignProduct;
        $this->_resource = $resource;
        $this->_moduleManager = $moduleManager;
        $this->_eavAttribute = $entityAttribute;
        $this->_modStatus = $modStatus;
        $this->productCollection = $productCollection;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->helper = $helper;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mppromotioncampaign:disable')
            ->setDescription('Marketplace Promotion Campaign Disable Command');
        parent::configure();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        if ($this->_moduleManager->isEnabled('Webkul_MpPromotionCampaign')) {

            $campaign =  $this->campaign->getCollection();
            foreach ($campaign as $cam) {
                $cam->delete();
            }
            $collection = $this->campaignProduct->create()->getCollection();
            foreach ($collection as $camProduct) {
                $productData = $this->product->load($camProduct->getProductId());
                $this->helper->updateSpecialPriceDates(
                    $productData->getSku(),
                    null,
                    '',
                    ''
                );
                $camProduct->delete();
            }
            $sellerCampaign = $this->sellerCampaign->getCollection();
            foreach ($sellerCampaign as $sellerCam) {
                $sellerCam->delete();
            }
            // disable
            $this->_modStatus->setIsEnabled(false, ['Webkul_MpPromotionCampaign']);

            $output->writeln('<info>Webkul Marketplace Promotion Campaign has been disabled successfully.</info>');
        }
    }
}
