<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Authorization\Model\Acl\Role\Group as RoleGroup;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class WholesaleUser implements
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
       * Create Wholesaler role
       */
        $role=$this->roleFactory->create();
        $role->setName('Wholesaler')->setPid(0)->setRoleType(RoleGroup::ROLE_TYPE)
                                   ->setUserType(UserContextInterface::USER_TYPE_ADMIN);
        $role->save();
        $resource = [
           'Webkul_MpWholesale::manager',
           'Webkul_MpWholesale::unit',
           'Webkul_MpWholesale::pricerule',
           'Webkul_MpWholesale::quotation',
           'Webkul_MpWholesale::leads',
           'Webkul_MpWholesale::productlist'
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
