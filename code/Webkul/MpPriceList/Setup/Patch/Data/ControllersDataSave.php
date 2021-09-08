<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPriceList\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class ControllersDataSave implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
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
        // setup default
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();
        /**
         * insert mppricelist controller's data
         */
        $data = [];

        if (!count($this->controllersRepository->getByPath('mppricelist/sellerpricelist/managepricelist'))) {
            $data[] = [
                'module_name' => 'Webkul_MpPriceList',
                'controller_path' => 'mppricelist/sellerpricelist/managepricelist',
                'label' => 'Manage Price List',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!count($this->controllersRepository->getByPath('mppricelist/pricerules/manageruleslist'))) {
            $data[] = [
                'module_name' => 'Webkul_MpPriceList',
                'controller_path' => 'mppricelist/pricerules/manageruleslist',
                'label' => 'Manage Price Rules',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (count($data)) {
            $connection->insertMultiple($this->moduleDataSetup->getTable('marketplace_controller_list'), $data);
            $this->moduleDataSetup->getConnection()->endSetup();
        }
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
        return [

        ];
    }
}
