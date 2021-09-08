<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpDailyDeal\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class CreateAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $statusSource = \Webkul\MpDailyDeal\Model\Config\Source\StatusOptions::class;
        
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_status',
            [
                'group' => 'Daily Deals',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Deal Status',
                'input' => 'select',
                'class' => '',
                'source' => $statusSource,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'is_used_in_grid' => true,
                'unique' => false
            ]
        );

        $typeSource = \Webkul\MpDailyDeal\Model\Config\Source\DiscountOptions::class;
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_discount_type',
            [
                'group' => 'Daily Deals',
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Discount Type',
                'input' => 'select',
                'class' => '',
                'source' => $typeSource,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_discount_percentage',
            [
                'group' => 'Daily Deals',
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'input' => 'hidden',
                'class' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false
            ]
        );

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_value',
            [
                'group'        => 'Daily Deals',
                'type'         => 'varchar',
                'backend'      => '',
                'frontend'     => '',
                'label'        => 'Deal Value',
                'input'        => 'text',
                'frontend_class' => 'required-entry validate-zero-or-greater',
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'      => true,
                'required'     => false,
                'user_defined' => false,
                'default'      => '',
                'searchable'   => false,
                'filterable'   => false,
                'comparable'   => false,
                'unique'       => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => true
            ]
        );

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_from_date',
            [
                'group'        => 'Daily Deals',
                'type'         => 'datetime',
                'backend'      => '',
                'frontend'     => '',
                'label'        => '',
                'input'        => 'hidden',
                'frontend_class' => '',
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'      => true,
                'required'     => false,
                'user_defined' => false,
                'default'      => '',
                'searchable'   => false,
                'filterable'   => false,
                'comparable'   => false,
                'unique'       => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => true
            ]
        );

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_to_date',
            [
                'group'        => 'Daily Deals',
                'type'         => 'datetime',
                'backend'      => '',
                'frontend'     => '',
                'label'        => '',
                'input'        => 'hidden',
                'frontend_class' => '',
                'global'       => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible'      => true,
                'required'     => false,
                'user_defined' => false,
                'default'      => '',
                'searchable'   => false,
                'filterable'   => false,
                'comparable'   => false,
                'unique'       => false,
                'visible_on_front'        => false,
                'used_in_product_listing' => true
            ]
        );
        $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'deal_status', 'is_user_defined', true);
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_discount_type',
            'is_user_defined',
            true
        );
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'deal_discount_percentage',
            'is_user_defined',
            true
        );
        $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'deal_value', 'is_user_defined', true);
        $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'deal_from_date', 'is_user_defined', true);
        $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'deal_to_date', 'is_user_defined', true);
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
