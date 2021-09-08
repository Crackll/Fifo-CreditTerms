<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\WebApplicationFirewall\Model\ResourceModel\FrontendTwoStepAuth;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var $_idFieldName
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var \Magento\Framework\App\Request\Http $request
     */
    protected $_eventPrefix = 'webkul_frontend_two_step_auth_collection';

    /**
     * @var \Magento\Framework\App\Request\Http $request
     */
    protected $_eventObject = 'webkul_frontend_two_step_auth_collection';

    /**
     * Define resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\WebApplicationFirewall\Model\FrontendTwoStepAuth::class,
            \Webkul\WebApplicationFirewall\Model\ResourceModel\FrontendTwoStepAuth::class
        );
    }
}
