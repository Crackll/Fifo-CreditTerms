<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */

namespace Webkul\WebApplicationFirewall\Controller\Adminhtml\Notification;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * WAF Login class
 */
class Login extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\WebApplicationFirewall\Model\AdminLoginFactory $adminLoginFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->adminLoginFactory = $adminLoginFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(0);
        $data = $this->getRequest()->getParams();

        try {

            $this->adminLoginFactory->create(['data' => $data])->processAdminLoginNotification();
            $status  = true;
            $message = __('Security email sent!');
            $response->setMessages($message);
            return $this->jsonResponse($response);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response->setError(true);
            $response->setMessages($e->getMessage());
            return $this->jsonResponse($response);
        } catch (\Exception $e) {
            $response->setError(true);
            $response->setMessages($e->getMessage());
            return $this->jsonResponse($response);
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}
