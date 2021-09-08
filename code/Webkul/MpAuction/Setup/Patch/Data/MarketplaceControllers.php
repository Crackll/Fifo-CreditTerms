<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

class MarketplaceControllers implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var \Webkul\Marketplace\Model\ControllersRepository
     */
    protected $controllersRepository;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ControllersRepository $controllersRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ControllersRepository $controllersRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->controllersRepository = $controllersRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $data = [];
        if (!count($this->controllersRepository->getByPath('mpauction/account/index'))) {
            $data[] = [
                'module_name' => 'Webkul_MpAuction',
                'controller_path' => 'mpauction/account/index',
                'label' => 'My Product List For Auction',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('mpauction/account/auctionlist'))) {
            $data[] = [
                'module_name' => 'Webkul_MpAuction',
                'controller_path' => 'mpauction/account/auctionlist',
                'label' => 'My Auction Product List',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('mpauction/account/addauction'))) {
            $data[] = [
                'module_name' => 'Webkul_MpAuction',
                'controller_path' => 'mpauction/account/addauction',
                'label' => 'Add Auction On Product',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('mpauction/account/auctionbiddetail'))) {
            $data[] = [
                'module_name' => 'Webkul_MpAuction',
                'controller_path' => 'mpauction/account/auctionbiddetail',
                'label' => 'Auction Bid Detail',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!empty($data)) {
            $this->moduleDataSetup->getConnection()->insertMultiple(
                $this->moduleDataSetup->getTable('marketplace_controller_list'),
                $data
            );
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
