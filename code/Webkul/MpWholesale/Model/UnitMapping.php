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

namespace Webkul\MpWholesale\Model;

use Webkul\MpWholesale\Api\Data\UnitMappingInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class UnitMapping extends AbstractModel implements UnitMappingInterface, IdentityInterface
{
    const CACHE_TAG = 'mpwholesale_unit_mapping';
    /**
     * @var string
     */
    protected $_cacheTag = 'mpwholesale_unit_mapping';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mpwholesale_unit_mapping';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpWholesale\Model\ResourceModel\UnitMapping::class);
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    public function setRuleId($ruleId)
    {
        return $this->setData(self::RULE_ID, $ruleId);
    }

    public function getUnitId()
    {
        return $this->getData(self::UNIT_ID);
    }

    public function setUnitId($unitId)
    {
        return $this->setData(self::UNIT_ID, $unitId);
    }

    public function getQty()
    {
        return $this->getData(self::QTY);
    }

    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    public function getQtyPrice()
    {
        return $this->getData(self::QTY_PRICE);
    }

    public function setQtyPrice($qtyPrice)
    {
        return $this->setData(self::QTY_PRICE, $qtyPrice);
    }
}
