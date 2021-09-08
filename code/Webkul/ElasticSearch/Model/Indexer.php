<?php
/**
 * Webkul Software.
 *
 * @category Webkul_ElasticSearch
 *
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ElasticSearch\Model;

use Webkul\ElasticSearch\Api\Data\IndexerInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * ElasticSearch Indexer Model.
 */
class Indexer extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, IndexerInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * ElasticSearch Indexer cache tag.
     */
    const CACHE_TAG = 'elastic_index';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'elastic_index';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\ElasticSearch\Model\ResourceModel\Indexer::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteReasons();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Indexer.
     *
     * @return \Webkul\Stripe\Model\Indexer
     */
    public function noRouteReasons()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\ElasticSearch\Api\Data\IndexerInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get Attributes.
     *
     * @return int
     */
    public function getAttributes()
    {
        $attributes = parent::getData(self::ATTRIBUTES);
        if ($attributes) {
            return json_decode($attributes, true);
        }
        return $attributes;
    }

    /**
     * Set attributes.
     *
     * @param array $attributes
     *
     * @return \Webkul\ElasticSearch\Api\Data\IndexerInterface
     */
    public function setAttributes($attributes)
    {
        $jsonString = '';
        if (is_array($attributes)) {
            $attributes = array_values($attributes);
            $jsonString = json_encode($attributes, true);
        }
        return $this->setData(self::ATTRIBUTES, $jsonString);
    }
}
