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

use Webkul\MpWholesale\Api\Data\PriceRuleInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class PriceRule extends AbstractModel implements PriceRuleInterface, IdentityInterface
{
    const CACHE_TAG = 'mpwholesale_price_rules';
    /**
     * @var string
     */
    protected $_cacheTag = 'mpwholesale_price_rules';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mpwholesale_price_rules';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpWholesale\Model\ResourceModel\PriceRule::class);
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

    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    public function getRuleName()
    {
        return $this->getData(self::RULE_NAME);
    }

    public function setRuleName($ruleName)
    {
        return $this->setData(self::RULE_NAME, $ruleName);
    }

    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getCreatedDate()
    {
        return $this->getData(self::CREATED_DATE);
    }

    public function setCreatedDate($createdDate)
    {
        return $this->setData(self::CREATED_DATE, $createdDate);
    }
}
