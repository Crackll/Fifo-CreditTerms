<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AccordionFaq
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AccordionFaq\Model;

use Webkul\AccordionFaq\Api\Data\FaqgroupInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Faqgroup extends \Magento\Framework\Model\AbstractModel implements FaqgroupInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * group's Status
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * AccordionFaq Faqgroup cache tag
     */
    const CACHE_TAG = 'accordionfaq_faqgroup';

    /**
     * @var string
     */
    protected $_cacheTag = 'accordionfaq_faqgroup';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'accordionfaq_faqgroup';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\AccordionFaq\Model\ResourceModel\Faqgroup::class);
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
            return $this->noRouteGroup();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Faqgroup
     *
     * @return \Webkul\AccordionFaq\Model\Faqgroup
     */
    public function noRouteGroup()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Prepare seller's statuses.
     * Available event accordionfaq_faqgroup_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Approved'), self::STATUS_DISABLED => __('Disapproved')];
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
     * @return \Webkul\AccordionFaq\Api\Data\FaqgroupInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
