<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Controller\Quotation;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\MpWholesale\Helper;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Webkul\MpWholesale\Model\QuoteconversationFactory;
use Magento\Framework\Controller\ResultFactory;

class Updatequote extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;
    /**
     * @var \Webkul\MpWholesale\Helper\Email
     */
    protected $mailHelper;
    /**
     * @var \Webkul\MpWholesale\Helper\Data
     */
    protected $helperData;
    /**
     * @var QuoteconversationFactory
     */
    protected $quoteconversationFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @param Context                                     $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param FormKeyValidator                            $formKeyValidator
     * @param QuoteconversationFactory                    $quoteConversationFactory
     * @param Helper\Email                                $helperMail
     * @param Helper\Data                                 $helperData
     */

    public function __construct(
        Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        FormKeyValidator $formKeyValidator,
        QuoteconversationFactory $quoteConversationFactory,
        Helper\Email $helperMail,
        Helper\Data $helperData
    ) {
        parent::__construct($context);
        $this->formKeyValidator = $formKeyValidator;
        $this->date = $date;
        $this->mailHelper = $helperMail;
        $this->quoteconversationFactory = $quoteConversationFactory;
        $this->helperData = $helperData;
    }

    /**
     * Save quote from buyer.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost()) {
            if (!$this->helperData->getModuleStatus()) {
                $this->messageManager->addError(__("Module is disabled by admin, Please contact to admin!"));
                return $resultRedirect->setPath(
                    'customer/account',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
            }
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                return $resultRedirect->setPath(
                    '*/*/view',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
            $wholedata = $this->getRequest()->getParams();
            $wholedata['msg_from'] = 'seller';
            $wholedata['created_at'] = $this->date->gmtDate();
            try {
                $entityId = $this->saveQuoteMessage($wholedata);
                if ($entityId) {
                    $this->messageManager->addSuccess(__('Quote message sent successfully !!'));
                    $this->mailHelper->messageQuote($wholedata);
                }
            } catch (\Exception $e) {
                $this->messageManager->addError("Unable to send quote message !!");
            }
            return $resultRedirect->setPath(
                '*/*/edit',
                ['_secure'=>$this->getRequest()->isSecure(), 'id' =>$wholedata['quote_id']]
            );
        }
        return $resultRedirect->setPath(
            '*/*/view',
            ['_secure'=>$this->getRequest()->isSecure()]
        );
    }

    /**
     * Save The Quote Conversation
     *
     * @param array $wholedata
     * @return integer
     */
    public function saveQuoteMessage($wholedata)
    {
        $quoteConversationModel = $this->quoteconversationFactory->create()
                    ->setData($wholedata)
                    ->save();
        $entityId = $quoteConversationModel->getId();
        return $entityId;
    }
}
