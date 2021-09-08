<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerApproval
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\CustomerApproval\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Model\Customer;

/**
 * Upgrade Data script
 *
 * @codeCoverageIgnore
 */
class AddCustomerAttribute implements DataPatchInterface
{
    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var \Webkul\CustomerApproval\Model\Attribute
     */
    protected $customerapprovalattribute;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @param EavSetupFactory $eavSetupFactory
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     * @param \Webkul\CustomerApproval\Model\Attribute $customerapprovalattribute
     * @param \Magento\Customer\Model\Customer $customer
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
        \Webkul\CustomerApproval\Model\Attribute $customerapprovalattribute,
        \Magento\Customer\Model\Customer $customer
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->customerapprovalattribute = $customerapprovalattribute;
        $this->customer = $customer;
    }

    public function apply()
    {
        /**
         * @var EavSetup $eavSetup
        */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        // removing the install attribute
        $eavSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, 'wk_customer_approval');

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerSetup->addAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'wk_customer_approval',
            [
                'type' => 'varchar',
                'label' => 'Customer Status',
                'input' => 'select',
                'source' => \Webkul\CustomerApproval\Model\Options::class,
                'required' => false,
                'default' => '1',
                'sort_order' => 100,
                'system' => false,
                'position' => 100,
                'lenght' => 3,
                'is_used_in_grid' => true
            ]
        );
        $attribute = $customerSetup->getEavConfig()->getAttribute(
            \Magento\Customer\Model\Customer::ENTITY,
            'wk_customer_approval'
        );
        $attribute->setData(
            'used_in_forms',
            [
                'adminhtml_customer_address', 'customer_address_edit',
                'customer_register_address', 'adminhtml_customer'
            ]
        );
        $attribute->save();
        $customerIds = $this->customer->getCollection()->getAllIds();
        $data = [
                    'wk_customer_approval' => '1'
        ];
        if (!empty($customerIds)) {
            $this->customerapprovalattribute->updateAttributes($customerIds, $data);
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
