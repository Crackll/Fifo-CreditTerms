<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

class InsertDefaultData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var ControllersRepository
     */
    private $controllersRepository;

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
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $data = [];
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();

        if (!count($this->controllersRepository->getByPath('mppushnotification/users/index'))) {
            $data[] = [
                'module_name' => 'Webkul_MpPushNotification',
                'controller_path' => 'mppushnotification/users/index',
                'label' => 'Registered Users',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        
        if (!count($this->controllersRepository->getByPath('mppushnotification/templates/index'))) {
            $data[] = [
                'module_name' => 'Webkul_MpPushNotification',
                'controller_path' => 'mppushnotification/templates/index',
                'label' => 'Notification Templates',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        $connection->insertMultiple($this->moduleDataSetup->getTable('marketplace_controller_list'), $data);
        $this->moduleDataSetup->getConnection()->endSetup();
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
