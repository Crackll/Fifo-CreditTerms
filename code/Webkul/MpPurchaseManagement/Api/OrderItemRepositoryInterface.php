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
 * OrderItemRepository Api interface.
 * @api
 */
interface OrderItemRepositoryInterface
{
    /**
     * Create or update an order Item.
     *
     * @param \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface $orderItem
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function save(\Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface $orderItem);

    /**
     * Get Order Item by orderId
     *
     * @param int $orderItemId
     * @return \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface
     */
    public function getById($orderItemId);

    /**
     * Delete Order Item.
     *
     * @param \Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface $orderItem
     * @return bool true on success
     */
    public function delete(\Webkul\MpPurchaseManagement\Api\Data\OrderItemInterface $orderItem);

    /**
     * Delete order Item by ID.
     *
     * @param int $orderItemId
     * @return bool true on success
     */
    public function deleteById($orderItemId);
}
