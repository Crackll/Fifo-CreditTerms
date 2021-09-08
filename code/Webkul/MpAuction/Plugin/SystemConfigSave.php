<?php
/**
 * Webkul_MpAuction Admin Config Incremental Price Save Controller.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Plugin;

class SystemConfigSave
{
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\Model\View\Result\Redirect $redirect,
        \Magento\Framework\App\Response\RedirectInterface $redirectInterface,
        \Webkul\MpAuction\Model\IncrementalPriceFactory $incrementPrice,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Webapi\Rest\Request $request
    ) {
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->incrementPrice = $incrementPrice;
        $this->redirectInterface = $redirectInterface;
        $this->_request = $request;
        $this->resultFactory = $resultFactory;
    }

    public function aroundExecute(
        \Magento\Config\Controller\Adminhtml\System\Config\Save $subject,
        \Closure $proceed,
        $requestInfo = null
    ) {
        $data = $this->_request->getParams();
        if (!isset($data['groups']['increment_option']['fields']['enable']['value'])) {
            $result = $proceed($requestInfo);
            return $result;
        } elseif (($this->incrementPrice->create()->getCollection()->getSize()
                && $this->incrementPrice->create()->getCollection()->getFirstItem()->getIncval()!='[]')
            || (isset($data['groups']['increment_option']['fields']['enable']['value'])
                && !$data['groups']['increment_option']['fields']['enable']['value'])) {
            $result = $proceed($requestInfo);
            return $result;
        } else {
            $redirectUrl = $this->redirectInterface->getRefererUrl();
            $this->messageManager->addError(
                __('You need to set the Incremental Price Range beforehand to enable Incremental Auction.')
            );
            $resultRedirect = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
            );
            $result = $resultRedirect->setUrl($redirectUrl);
            return $result;
        }
    }
}
