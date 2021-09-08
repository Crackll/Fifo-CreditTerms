<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomRegistration
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\CustomRegistration\Controller\Adminhtml\Customfields;

use Webkul\CustomRegistration\Controller\Adminhtml\AbstractMassDisplayEmail;

/**
 * Controller HideInEmail
 */
class HideInEmail extends AbstractMassDisplayEmail
{
    /**
     * Field id
     */
    const ID_FIELD = 'entity_id';

    /**
     * Resource collection
     *
     * @var string
     */
    protected $collection = \Webkul\CustomRegistration\Model\ResourceModel\Customfields\Collection::class;

    /**
     * Post model
     *
     * @var string
     */
    protected $model = \Webkul\CustomRegistration\Model\Customfields::class;

    /**
     * Post enable status
     *
     * @var boolean
     */
    protected $status = false;
}
