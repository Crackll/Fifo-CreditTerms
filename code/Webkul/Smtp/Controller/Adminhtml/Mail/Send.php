<?php
/**
 * Webkul Smtp send test mail Controller.
 * @category  Webkul
 * @package   Webkul_Smtp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Smtp\Controller\Adminhtml\Mail;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\Smtp\Helper\Email as SmtpEmailHelper;

class Send extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context     $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SmtpEmailHelper $smtpEmailHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->smtpEmailHelper = $smtpEmailHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Smtp::config_testmail');
    }

    /**
     * Webkul_Smtp test mail send controller
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        try {
            $data = $this->getRequest()->getParams();
            $this->smtpEmailHelper->sendTestMailForSmtp($data);
            return $resultJson->setData(['msg' => 'Mail send successfully...']);
        } catch (\Exception $e) {
            $result['msg'] = $e->getMessage();
            return $resultJson->setData($result);
        }
    }
}
