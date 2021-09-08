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

namespace Webkul\WebApplicationFirewall\Controller\Adminhtml\Config;

use Magento\Framework\App\Filesystem\DirectoryList;
use Webkul\WebApplicationFirewall\Api\ResetSubuserPasswordInterface;

/**
 * WAF ResetPassword class
 */
class ResetPassword extends \Magento\Backend\App\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $resultPageFactory;

    /** @var \Magento\Framework\Json\Helper\Data */
    protected $jsonHelper;

    /** @var \Webkul\WebApplicationFirewall\Helper\Data */
    protected $dataHelper;

    /** @var ResetSubuserPasswordInterface */
    protected $resetPassword;

    /** @var DirectoryList */
    protected $directoryList;

    /**
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\WebApplicationFirewall\Helper\Data $dataHelper
     * @param DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\WebApplicationFirewall\Helper\Data $dataHelper,
        ResetSubuserPasswordInterface $resetPassword,
        DirectoryList $directoryList
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->dataHelper = $dataHelper;
        $this->resetPassword = $resetPassword;
        $this->_directoryList    = $directoryList;
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
        /** @var \Magento\User\Model\User $user*/
        $user = $this->_auth->getUser();
        try {
            $this->resetPassword->setUser($user)
                ->sendResetRequest();
            $message = __('Reset password notification sent to all!');
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
