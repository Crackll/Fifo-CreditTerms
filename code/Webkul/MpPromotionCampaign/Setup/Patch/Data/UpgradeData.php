<?php
/**
 * Webkul MpPromotionCampaign Data Setup
 * @category  Webkul
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPromotionCampaign\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

class UpgradeData implements DataPatchInterface
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
        if (!count($this->controllersRepository->getByPath('mppromotioncampaign/promotion/index'))) {
            $data[] = [
                'module_name' => 'Webkul_MpPromotionCampaign',
                'controller_path' => 'mppromotioncampaign/promotion/index',
                'label' => 'Marketplace Promotion Campaign Grid',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('mppromotioncampaign/campaign/edit'))) {
            $data[] = [
                'module_name' => 'Webkul_MpPromotionCampaign',
                'controller_path' => 'mppromotioncampaign/campaign/edit',
                'label' => 'Marketplace Promotion Campaign Edit',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('mppromotioncampaign/campaign/join'))) {
            $data[] = [
                'module_name' => 'Webkul_MpPromotionCampaign',
                'controller_path' => 'mppromotioncampaign/campaign/join',
                'label' => 'Marketplace Promotion Campaign Join',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('mppromotioncampaign/campaign/index'))) {
            $data[] = [
                'module_name' => 'Webkul_MpPromotionCampaign',
                'controller_path' => 'mppromotioncampaign/campaign/index',
                'label' => 'Marketplace Promotion Campaign Index',
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
