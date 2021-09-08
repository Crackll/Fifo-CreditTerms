<?php
/**
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PurchaseOrderUser implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * RoleFactory
     *
     * @var roleFactory
     */
    private $roleFactory;

     /**
      * RulesFactory
      *
      * @var rulesFactory
      */
    private $rulesFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Authorization\Model\RoleFactory $roleFactory,
        \Magento\Authorization\Model\RulesFactory $rulesFactory
    ) {
    
        $this->moduleDataSetup = $moduleDataSetup;
        $this->roleFactory = $roleFactory;
        $this->rulesFactory = $rulesFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
      /**
        * Assign resources to Wholesaler role
        */
        $role=$this->roleFactory->create()->getCollection()
                                ->addFieldToFilter('role_name', ['eq'=>'Wholesaler'])
                                ->setPageSize(1)->getFirstItem();
        $resource = [
            'Webkul_MpWholesale::manager',
            'Webkul_MpWholesale::unit',
            'Webkul_MpWholesale::pricerule',
            'Webkul_MpWholesale::quotation',
            'Webkul_MpWholesale::leads',
            'Webkul_MpWholesale::productlist',
            'Webkul_MpPurchaseManagement::menu',
            'Webkul_MpPurchaseManagement::purchaseorders'
        ];
        $this->rulesFactory->create()->setRoleId($role->getId())->setResources($resource)->saveRel();
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
