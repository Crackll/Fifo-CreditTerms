<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRewardSystem\Setup\Patch\Data;

use Magento\Framework\Setup;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
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
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    protected $attributeSetFactory;
    /**
     * Undocumented function
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param Setup\SampleData\Executor $executor
     * @param CustomerSetupFactory $customerSetupFactory
     * @param ControllersRepository $controllersRepository
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        Setup\SampleData\Executor $executor,
        CustomerSetupFactory $customerSetupFactory,
        ControllersRepository $controllersRepository,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->executor = $executor;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->controllersRepository = $controllersRepository;
        $this->attributeSetFactory = $attributeSetFactory;
    }
    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->installData($this->moduleDataSetup);
    }

    /**
     * Copy Banner and Icon Images to Media
     */
    public function installData($setup)
    {
       /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

       /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $customerSetup->addAttribute(Customer::ENTITY, 'rewardprice', [
           'type' => 'varchar',
           'label' => 'Reward Price',
           'input' => 'text',
           'frontend_class' => 'validate-number',
           'required' => false,
           'visible' => true,
           'user_defined' => true,
           'sort_order' => 1000,
           'position' => 1000,
           'system' => 0,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'rewardprice')
        ->addData([
           'attribute_set_id' => $attributeSetId,
           'attribute_group_id' => $attributeGroupId,
           'used_in_forms' => ['adminhtml_customer'],
        ]);

        $attribute->save();

        $customerSetup->addAttribute(Customer::ENTITY, 'reward_priority', [
           'type' => 'int',
           'label' => 'Reward Priority',
           'input' => 'text',
           'frontend_class' => '',
           'required' => false,
           'visible' => true,
           'user_defined' => true,
           'sort_order' => 1000,
           'position' => 1000,
           'default' => 0,
           'system' => 0,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'reward_priority')
        ->addData([
           'attribute_set_id' => $attributeSetId,
           'attribute_group_id' => $attributeGroupId,
           'used_in_forms' => ['adminhtml_customer'],
        ]);

        $attribute->save();

        $customerSetup->addAttribute(Customer::ENTITY, 'reward_product_status', [
           'type' => 'int',
           'label' => 'Reward Product State',
           'input' => 'boolean',
           'frontend_class' => '',
           'required' => false,
           'visible' => true,
           'user_defined' => true,
           'sort_order' => 1000,
           'position' => 1000,
           'default' => 0,
           'system' => 0,
        ]);

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'reward_product_status')
        ->addData([
           'attribute_set_id' => $attributeSetId,
           'attribute_group_id' => $attributeGroupId,
           'used_in_forms' => ['adminhtml_customer'],
        ]);

        $attribute->save();

        $setup->startSetup();

       /**
        * insert MpRewardSystem controller's data
        */
        $data = [];

        if (!empty($this->controllersRepository->getByPath('mprewardsystem/account/index'))) {
            $data[] = [
               'module_name' => 'Webkul_MpRewardSystem',
               'controller_path' => 'mprewardsystem/account/index',
               'label' => 'Manage Rewards',
               'is_child' => '0',
               'parent_id' => '0',
            ];
        }

        if (!empty($this->controllersRepository->getByPath('mprewardsystem/account/cart'))) {
            $data[] = [
               'module_name' => 'Webkul_MpRewardSystem',
               'controller_path' => 'mprewardsystem/account/cart',
               'label' => 'Manage Cart Reward',
               'is_child' => '0',
               'parent_id' => '0',
            ];
        }

        if (!empty($this->controllersRepository->getByPath('mprewardsystem/product/index'))) {
            $data[] = [
               'module_name' => 'Webkul_MpRewardSystem',
               'controller_path' => 'mprewardsystem/product/index',
               'label' => 'Manage Product Reward',
               'is_child' => '0',
               'parent_id' => '0',
            ];
        }

        if (!empty($this->controllersRepository->getByPath('mprewardsystem/category/index'))) {
            $data[] = [
               'module_name' => 'Webkul_MpRewardSystem',
               'controller_path' => 'mprewardsystem/category/index',
               'label' => 'Manage Category Reward',
               'is_child' => '0',
               'parent_id' => '0',
            ];
        }

        $setup->getConnection()
           ->insertMultiple($setup->getTable('marketplace_controller_list'), $data);

        $setup->endSetup();
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
