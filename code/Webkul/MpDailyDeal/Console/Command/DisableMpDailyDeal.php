<?php
/**
 * Webkul Software
 *
 * @category Magento
 * @package  Webkul_MpDailyDeal
 * @author   Webkul
 * @license  https://store.webkul.com/license.html
 */
namespace Webkul\MpDailyDeal\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DisableDailyDeals
 */
class DisableMpDailyDeal extends Command
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

    /**
     * @param \Magento\Eav\Setup\EavSetupFactory        $eavSetupFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Module\Manager         $moduleManager
     * @param \Magento\Framework\Module\Status          $modStatus
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Framework\Module\Status $modStatus,
        \Magento\Framework\App\State $appState,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
    ) {
        $this->_resource = $resource;
        $this->_moduleManager = $moduleManager;
        $this->_eavAttribute = $entityAttribute;
        $this->_modStatus = $modStatus;
        $this->productCollection = $productCollection;
        $this->appState = $appState;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mpdailydeal:disable')
            ->setDescription('Marketplace Daily Deal Disable Command');
        parent::configure();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode('adminhtml');
        if ($this->_moduleManager->isEnabled('Webkul_MpDailyDeal')) {
            $collection = $this->productCollection->create()->addAttributeToFilter('deal_status', 1);
            foreach ($collection as $product) {
                $product->setSpecialToDate('');
                $product->setSpecialFromDate('');
                $product->setSpecialPrice(null);
                $this->saveObject($product);
            }

            $connection = $this->_resource
                ->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
            
            // delete deal product attribute
            $this->_eavAttribute->loadByCode('catalog_product', 'deal_status')->delete();
            $this->_eavAttribute->loadByCode('catalog_product', 'deal_discount_type')->delete();
            $this->_eavAttribute->loadByCode('catalog_product', 'deal_discount_percentage')->delete();
            $this->_eavAttribute->loadByCode('catalog_product', 'deal_value')->delete();
            $this->_eavAttribute->loadByCode('catalog_product', 'deal_from_date')->delete();
            $this->_eavAttribute->loadByCode('catalog_product', 'deal_to_date')->delete();

            // disable daily deals
            $this->_modStatus->setIsEnabled(false, ['Webkul_MpDailyDeal']);
            // delete entry from setup_module table
            $tableName = $this->_resource->getTableName('patch_list');
            $connection->delete($tableName, "patch_name like 'Webkul%MpDailyDeal%'");
            $tableName = $this->_resource->getTableName('setup_module');
            $connection->delete($tableName, "module = 'Webkul_MpDailyDeal'");
            $output->writeln('<info>Webkul Marketplace Daily Deal has been disabled successfully.</info>');
        }
    }
    private function saveObject($object)
    {
        $object->save();
    }
}
