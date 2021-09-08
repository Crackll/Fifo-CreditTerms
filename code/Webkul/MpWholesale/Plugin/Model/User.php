<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Plugin\Model;

use Webkul\MpWholesale\Model\ProductFactory;

class User
{

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @param ProductFactory $productFactory
     */
    public function __construct(
        ProductFactory $productFactory
    ) {
        $this->productFactory = $productFactory;
    }

    public function afterDelete(\Magento\User\Model\ResourceModel\User $subject, $result, $user)
    {
        $userId = $user->getId();
        $productCollection = $this->productFactory->create()
                                 ->getCollection()
                                 ->addFieldToFilter('user_id', $userId);
        foreach ($productCollection as $product) {
            $product->delete();
        }
        return $result;
    }
}
