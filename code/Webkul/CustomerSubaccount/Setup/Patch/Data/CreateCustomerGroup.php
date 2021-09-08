<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerSubaccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\CustomerSubaccount\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

class CreateCustomerGroup implements DataPatchInterface
{
    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var CollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @param GroupFactory          $groupFactory
     * @param CollectionFactory     $groupCollectionFactory
     */
    public function __construct(
        GroupFactory $groupFactory,
        CollectionFactory $groupCollectionFactory
    ) {
        $this->groupFactory = $groupFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    public function apply()
    {
        $groupCollection = $this->groupCollectionFactory->create()
                                ->addFieldToFilter('customer_group_code', 'Customer Subaccount');
        if (!$groupCollection->getSize()) {
            // Create the new group
            /** @var \Magento\Customer\Model\Group $group */
            $group = $this->groupFactory->create();
            $group->setCode('Customer Subaccount')
                ->setTaxClassId(3)
                ->save();
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
        return [];
    }
}
