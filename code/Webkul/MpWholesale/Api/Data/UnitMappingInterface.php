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

namespace Webkul\MpWholesale\Api\Data;

interface UnitMappingInterface
{
    const ENTITY_ID                 = 'entity_id';
    const RULE_ID                   = 'rule_id';
    const UNIT_ID                   = 'unit_id';
    const QTY                       = 'qty';
    const QTY_PRICE                 = 'qty_price';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Get Rule ID
     *
     * @return int|null
     */
    public function getRuleId();

    /**
     * Get Unit Id
     *
     * @return int|null
     */
    public function getUnitId();

    /**
     * Get Qty
     *
     * @return int|null
     */
    public function getQty();

    /**
     * Get Qty Price
     *
     * @return float|null
     */
    public function getQtyPrice();

    /**
     * Set ID
     *
     * @return int|null
     */
    public function setEntityId($id);

    /**
     * Set Rule ID
     *
     * @return int|null
     */
    public function setRuleId($ruleId);

    /**
     * Set Unit Id
     *
     * @return int|null
     */
    public function setUnitId($unitId);

    /**
     * Set Qty
     *
     * @return int|null
     */
    public function setQty($qty);

    /**
     * Set Qty Price
     *
     * @return float|null
     */
    public function setQtyPrice($qtyPrice);
}
