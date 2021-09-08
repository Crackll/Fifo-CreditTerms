<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Model;

use Webkul\Helpdesk\Api\Data\TypeInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk Type Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\Type _getResource()
 * @method \Webkul\Helpdesk\Model\ResourceModel\Type getResource()
 */
class Type extends \Magento\Framework\Model\AbstractModel implements TypeInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk Type cache tag
     */
    const CACHE_TAG = 'helpdesk_tickets_type';

    /**#@+
    * Product's Statuses
    */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;
    /**#@-*/

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_tickets_type';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_tickets_type';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\Type::class);
    }

    /**
     * Load object data
     *
     * @param int|null $id
     * @param string $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteType();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Type
     *
     * @return \Webkul\Helpdesk\Model\Type
     */
    public function noRouteType()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Helpdesk\Api\Data\TypeInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Prepare product's statuses.
     * Available event marketplace_product_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enable'),
            self::STATUS_DISABLED => __('Disable'),
        ];
    }

    /**
     * toOptionArray Return status option array
     */
    public function toOptionArray()
    {
        $data = [];
        $typeCollection = $this->getCollection()
        ->addFieldToFilter("status", ["eq"=>1]);
        foreach ($typeCollection as $type) {
            $data[] =  ['value'=>$type->getId(), 'label'=>$type->getTypeName()];
        }
        return  $data;
    }
}
