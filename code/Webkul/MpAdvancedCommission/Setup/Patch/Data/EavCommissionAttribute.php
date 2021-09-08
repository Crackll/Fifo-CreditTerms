<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAdvancedCommission
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvancedCommission\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Customer;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class EavCommissionAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $_eavSetupFactory;

     /**
      * @var CustomerSetupFactory
      */
    protected $_customerSetupFactory;
    
    /**
     * @var AttributeSetFactory
     */
    private $_attributeSetFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_customerSetupFactory = $customerSetupFactory;
        $this->_attributeSetFactory = $attributeSetFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $attrCode = 'commission_for_product';
        $attrGroupName = __('Prices');
        $attrLabel = __('Commission For Product');
        $attrNote = __('Commission Per Product');
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attrCodeExist = $eavSetup->getAttributeId(Product::ENTITY, $attrCode);
        if ($attrCodeExist === false) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                $attrCode,
                [
                    'group'                 => $attrGroupName,
                    'type'                  => 'varchar',
                    'input'                 => 'text',
                    'backend'               => '',
                    'frontend'              => '',
                    'label'                 => $attrLabel,
                    'note'                  => $attrNote,
                    'frontend_class'        => 'validate-number',
                    'source'                => '',
                    'global'                => Attribute::SCOPE_GLOBAL,
                    'visible'               => true,
                    'user_defined'          => true,
                    'required'              => false,
                    'visible_on_front'      => false,
                    'unique'                => false,
                    'is_configurable'       => false,
                    'used_for_promo_rules'  => true
                ]
            );
        }

        $attrCode = 'commission_for_admin';
        $attrLabel = 'Commission For Admin';
        $attrNote = 'Commission that goes to Admin';
        $attrGroupName = 'General Information';
        $eavSetup = $this->_eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            'catalog_category',
            $attrCode,
            [
                'type'                  => 'varchar',
                'group'                 => $attrGroupName,
                'input'                 => 'text',
                'backend'               => '',
                'frontend'              => '',
                'label'                 => $attrLabel,
                'note'                  => $attrNote,
                'class'                 => 'validate-zero-or-greater',
                'source'                => '',
                'global'                => Attribute::SCOPE_GLOBAL,
                'visible'               => true,
                'user_defined'          => false,
                'required'              => false,
                "default"               => "",
                "searchable"            => false,
                "filterable"            => false,
                "comparable"            => false,
                'visible_on_front'      => false,
                'unique'                => false
            ]
        );

        /* Create Customer Attribute*/

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->_customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();
        
        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->_attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'category_commission',
            [
                'type'              => 'text',
                'label'             => 'Category Commission',
                'input'             => 'text',
                'frontend_class'    => 'validate-zero-or-greater',
                'system'            => false,
                'global'            => true,
                'visible'           => false,
                'required'          => false,
                'user_defined'      => true,
                'default'           => '0'
            ]
        );

        $attribute = $customerSetup->getEavConfig()
        ->getAttribute(
            Customer::ENTITY,
            'category_commission'
        )
        ->addData(
            [
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => [
                    'adminhtml_customer'
                ]
            ]
        );
        $attribute->save();
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
