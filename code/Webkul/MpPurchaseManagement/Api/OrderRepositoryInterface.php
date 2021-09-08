<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpPurchaseManagement
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPurchaseManagement\Api;

/**
 * OrderRepository Api interface.
 * @api
 */
interface OrderRepositoryInterface
{
    /**
     * Create or update an order.
     *
     * @param \Webkul\MpPurchaseManagement\Api\Data\OrderInterface $order
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderInterface
     */
    public function save(\Webkul\MpPurchaseManagement\Api\Data\OrderInterface $order);

    /**
     * Get Order by orderId
     *
     * @param int $orderId
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderInterface
     */
    public function getById($orderId);

    /**
     * Delete Order.
     *
     * @param \Webkul\MpPurchaseManagement\Api\Data\OrderInterface $order
     * @return bool true on success
     */
    public function delete(\Webkul\MpPurchaseManagement\Api\Data\OrderInterface $order);

    /**
     * Delete order by ID.
     *
     * @param int $orderId
     * @return bool true on success
     */
    public function deleteById($orderId);
}
