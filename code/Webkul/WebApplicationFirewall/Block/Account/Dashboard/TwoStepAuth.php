<?php
/**
 * Webkul Software Pvt. Ltd.
 *
 * @category  Webkul
 * @package   Webkul_WebApplicationFirewall
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\WebApplicationFirewall\Block\Account\Dashboard;

class TwoStepAuth extends \Magento\Framework\View\Element\Template
{
    /**
     * @param \Webkul\WebApplicationFirewall\Helper\TwoStepAuthHelper
     */
    private $twoStepAuthHelper;

    /**
     * Construct
     * @param \Magento\Framework\View\Element\Template\Context          $context
     * @param \Webkul\WebApplicationFirewall\Helper\TwoStepAuthHelper   $twoStepAuthHelper
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\WebApplicationFirewall\Helper\TwoStepAuthHelper $twoStepAuthHelper
    ) {
        parent::__construct($context);
        $this->twoStepAuthHelper = $twoStepAuthHelper;
    }

    /**
     * Get Config Value
     * @param string
     * @return string|int|float|null
     */
    public function getConfigValue($path)
    {
        return $this->twoStepAuthHelper->getConfigValue($path);
    }

    /**
     * check module enable
     * @param void
     * @return boolean
     */
    public function isModuleEnable()
    {
        return $this->twoStepAuthHelper->isModuleEnable();
    }

    /**
     * check is new user
     * @param void
     * @return boolean
     */
    public function isNewUser()
    {
        return $this->twoStepAuthHelper->isNewUser();
    }

    /**
     * check is auth enabled
     * @param void
     * @return boolean
     */
    public function getIsAuthEnabled()
    {
        return $this->twoStepAuthHelper->getIsAuthEnabled();
    }

    /**
     * check is authentication form url
     * @param void
     * @return string
     */
    public function getAuthenticationFormUrl()
    {
        return $this->twoStepAuthHelper->getAuthenticationFormUrl();
    }
}
