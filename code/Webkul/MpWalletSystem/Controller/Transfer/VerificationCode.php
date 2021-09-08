<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Controller\Transfer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Webkul MpWalletSystem Controller
 */
class VerificationCode extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * Initialize dependencies
     *
     * @param Context                                            $context
     * @param \Magento\Framework\Serialize\Serializer\Base64Json $base64
     * @param PageFactory                                        $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Serialize\Serializer\Base64Json $base64,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->base64 = $base64;
        parent::__construct($context);
    }
    
    /**
     * Controller Execute function
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = [];
        /**
         * @var \Magento\Framework\View\Result\Page $resultPage
         */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(
            __('Verification Code')
        );
        $getEncodedParamData = $this->getRequest()->getParam('parameter');
        $params = $this->base64->unserialize(urldecode($getEncodedParamData));
        if (is_array($params) && !empty($params) && array_key_exists('sender_id', $params)) {
            return $resultPage;
        } else {
            $this->messageManager->addError(__("Please try again later."));
            return $this->resultRedirectFactory->create()->setPath(
                'mpwalletsystem/transfer/index',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
