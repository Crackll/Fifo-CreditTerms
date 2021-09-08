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

namespace Webkul\MpWalletSystem\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableWalletsystem extends Command
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Framework\Module\Status
     */
    protected $modStatus;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $dataSetupFactory
     * @param \Magento\Framework\App\ResourceConnection         $resource
     * @param \Magento\Framework\Module\Manager                 $moduleManager
     * @param \Magento\Eav\Model\Entity\Attribute               $entityAttribute
     * @param \Magento\Framework\Module\Status                  $modStatus
     * @param \Magento\Catalog\Model\ProductRepository          $productRepository
     * @param \Magento\Framework\Registry                       $registry
     * @param \Magento\Framework\App\State                      $appstate
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $dataSetupFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Framework\Module\Status $modStatus,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\State $appstate
    ) {
        $this->setupFactory = $dataSetupFactory;
        $this->resource = $resource;
        $this->moduleManager = $moduleManager;
        $this->eavAttribute = $entityAttribute;
        $this->modStatus = $modStatus;
        $this->productRepository = $productRepository;
        $this->registry = $registry;
        $this->appState = $appstate;
        parent::__construct();
    }
    
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('mpwalletsystem:disable')
            ->setDescription('Marketplace Walletsystem Disable Command');
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->moduleManager->isEnabled('Webkul_MpWalletSystem')) {
            $connection = $this->resource
                ->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);

            // drop custom tables
            $connection->dropTable($connection->getTableName('wk_ws_credit_rules'));
            $connection->dropTable($connection->getTableName('wk_ws_credit_amount'));
            $connection->dropTable($connection->getTableName('wk_ws_wallet_record'));
            $connection->dropTable($connection->getTableName('wk_ws_wallet_transaction'));
            
            // delete wallet_credit_based_on product attribute
            $this->eavAttribute->loadByCode('catalog_product', 'wallet_credit_based_on')->delete();
            // delete wallet_cash_back product attribute
            $this->eavAttribute->loadByCode('catalog_product', 'wallet_cash_back')->delete();
            
            // delete product
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
            $this->registry->register('isSecureArea', true);
            // using sku
            $this->productRepository->deleteById(\Webkul\MpWalletSystem\Helper\Data::WALLET_PRODUCT_SKU);
            // disable walletsystem module
            $this->modStatus->setIsEnabled(false, ['Webkul_MpWalletSystem']);

            // delete entry from setup_module table
            $setup = $this->setupFactory->create();
            $setup->deleteTableRow('setup_module', 'module', 'Webkul_MpWalletSystem');
            $output->writeln('<info>Webkul Marketplace Walletsystem module has been disabled successfully.</info>');
        }
    }
}
