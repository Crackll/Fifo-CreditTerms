<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SampleProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SampleProduct\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul SampleProduct ControllerActionPredispatch Observer.
 */
class ControllerActionPredispatch implements ObserverInterface
{
    /**
     * @var \Webkul\SampleProduct\Helper\Data
     */
    private $helper;

    /**
     * @param \Webkul\SampleProduct\Helper\Data $helper
     */

    public function __construct(
        \Webkul\SampleProduct\Helper\Data $helper,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->redirect = $redirect;
        $this->request = $request;
        
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $action = $this->request->getFullActionName();
       
        if($action !=  "loginascustomer_login_index"){
            $request = $observer->getControllerAction()->getRequest();
            $postData = $request->getParams();
            if (empty($request->getRouteName())) {
                return false;
            } else if ($request->getRouteName() == 'catalog') {
                if (!empty($postData['id'])) {
                    $productId = $postData['id'];
                    if ($this->helper->isCurrentSampleProduct($productId)) {
                        $parentProduct = $this->helper->getSampleParentProduct($productId);
                        $productUrl = $parentProduct->getProductUrl();
                        $actionName = $observer->getEvent()->getRequest()->getFullActionName();
                        $controller = $observer->getControllerAction();
                        $this->redirect->redirect($controller->getResponse(), $productUrl);
                    }
                }
            }
        }
        
    }
}
