<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_CustomerSubaccount
 * @author     Webkul
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\CustomerSubaccount\Plugin;

class ReviewView
{
    public $context;
    public $response;
    public $redirect;
    public $url;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\UrlInterface $url,
        \Webkul\CustomerSubaccount\Helper\Data $helper
    ) {
        $this->context = $context;
        $this->helper = $helper;
        $this->response = $context->getResponse();
        $this->redirect = $context->getRedirect();
        $this->url = $url;
    }

    public function aroundExecute(
        \Magento\Review\Controller\Customer\View $object,
        callable $proceed
    ) {
        if ($this->helper->isSubaccountUser() && !$this->helper->canReviewProducts()) {
            $norouteUrl = $this->url->getUrl('noroute');
            $this->getResponse()->setRedirect($norouteUrl);
            return;
        } else {
            return $proceed();
        }
    }
    
    /**
     * Retrieve response object
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }
}
