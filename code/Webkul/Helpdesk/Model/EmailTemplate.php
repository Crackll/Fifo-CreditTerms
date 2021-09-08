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

use Webkul\Helpdesk\Api\Data\EmailTemplateInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Helpdesk EmailTemplate Model
 *
 * @method \Webkul\Helpdesk\Model\ResourceModel\EmailTemplate _getResource()
 * @method \Webkul\Helpdesk\Model\ResourceModel\EmailTemplate getResource()
 */
class EmailTemplate extends \Magento\Framework\Model\AbstractModel implements EmailTemplateInterface, IdentityInterface
{
    /**
     * No route page id
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Helpdesk EmailTemplate cache tag
     */
    const CACHE_TAG = 'helpdesk_ticket_email_template';

    /**
     * @var string
     */
    protected $_cacheTag = 'helpdesk_ticket_email_template';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'helpdesk_ticket_email_template';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Helpdesk\Model\ResourceModel\EmailTemplate::class);
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
            return $this->noRouteEmailTemplate();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route EmailTemplate
     *
     * @return \Webkul\Helpdesk\Model\EmailTemplate
     */
    public function noRouteEmailTemplate()
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
     * @return \Webkul\Helpdesk\Api\Data\EmailTemplateInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
