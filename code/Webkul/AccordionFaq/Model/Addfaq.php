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

use Webkul\AccordionFaq\Api\Data\AddfaqInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Addfaq extends \Magento\Framework\Model\AbstractModel implements AddfaqInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * faq's Status
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * AccordionFaq Addfaq cache tag
     */
    const CACHE_TAG = 'accordionfaq_addfaq';

    /**
     * @var string
     */
    protected $_cacheTag = 'accordionfaq_addfaq';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'accordionfaq_addfaq';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\AccordionFaq\Model\ResourceModel\Addfaq::class);
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
            return $this->noRouteFaq();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Images
     *
     * @return \Webkul\AccordionFaq\Model\Addfaq
     */
    public function noRouteFaq()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Prepare seller's statuses.
     * Available event accordionfaq_addfaq_get_available_statuses to customize statuses.
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
     * @return \Webkul\AccordionFaq\Api\Data\AddfaqInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
