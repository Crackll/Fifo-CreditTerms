<?php
/**
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Controller\Adminhtml\Quotation;

use Magento\Backend\App\Action;
use Webkul\MpWholesale\Helper;

class Save extends \Webkul\MpWholesale\Controller\Adminhtml\Quotation
{

    /**
     * @var \Webkul\MpWholesale\Model\QuotesFactory
     */
    protected $quotesFactory;

   /**
    * @var \Webkul\MpWholesale\Model\QuoteconversationFactory
    */
    protected $quoteConversationFactory;
   
   /**
    * @var \Magento\Framework\Stdlib\DateTime\DateTime
    */
    protected $date;
   /**
    * @var \Webkul\MpWholesale\Helper\Email
    */
    protected $mailHelper;
   /**
    * @var \Webkul\MpWholesale\Helper\Data
    */
    protected $helperData;

    /**
     * @param Action\Context $context
     * @param \Webkul\MpWholesale\Model\QuotesFactory $quotesFactory
     * @param \Webkul\MpWholesale\Model\QuoteconversationFactory $quoteConversationFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\MpWholesale\Helper\Email $mailHelper
     * @param \Webkul\MpWholesale\Helper\Data $helperData
     */
    public function __construct(
        Action\Context $context,
        \Webkul\MpWholesale\Model\QuotesFactory $quotesFactory,
        \Webkul\MpWholesale\Model\QuoteconversationFactory $quoteConversationFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Helper\Email $helperMail,
        Helper\Data $helperData
    ) {
        $this->quotesFactory = $quotesFactory;
        $this->quoteConversationFactory = $quoteConversationFactory;
        $this->date = $date;
        $this->mailHelper = $helperMail;
        $this->helperData = $helperData;
        parent::__construct(
            $context
        );
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        $quoteId = 0;
        if (array_key_exists('id', $data)) {
            $quoteId = $data['id'];
        }
        if ($this->getRequest()->isPost()) {
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                return $resultRedirect->setPath(
                    '*/*/index',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
            }
            if ($data['conversations'] == '') {
                $this->messageManager->addError(
                    __('Quote Message cannot be empty!!!')
                );
                return $resultRedirect->setPath(
                    '*/*/edit',
                    ['_secure'=>$this->getRequest()->isSecure(), 'id'=> $quoteId]
                );
            }
            $quote = $this->quotesFactory->create()->load($quoteId);
            if ($quote->getEntityId()) {
                $this->checkAndUpdateData($data, $quote);
            } else {
                $this->messageManager->addError(
                    __('Quote is not exists.')
                );
                $quoteId = 0;
            }
            if (!$quoteId) {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/index',
                    ['_secure'=>$this->getRequest()->isSecure()]
                );
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/edit',
                    ['_secure'=>$this->getRequest()->isSecure(), 'id'=> $quoteId]
                );
            }
        }
    }

    public function checkAndUpdateData($data, $quote)
    {
        $status = 0;
        try {
            if ($quote->getStatus() == 2 && $data['status'] == 3) {
                return $this->messageManager->addError(__("Quote isn't declined after approved"));
            }

            if ($quote->getStatus() != $data['status']) {
                $status = 1;
                $quote->setStatus($data['status']);
                $quote->save();
            }
            $conversationData = [
                'sender_id' => $data['sender_id'],
                'receiver_id' => $data['receiver_id'],
                'conversation' => $data['conversations'],
                'quote_id'  => $data['quote_id'],
                'msg_from'  => 'wholesaler',
                'created_at' => $this->date->gmtDate()
            ];
            $quoteConversationModel = $this->quoteConversationFactory->create()
                        ->setData($conversationData)
                        ->save();
            $entityId = $quoteConversationModel->getId();
            if ($entityId) {
                $this->messageManager->addSuccess(__('Quote message sent successfully !!'));
                if ($status) {
                    $this->mailHelper->messageQuoteStatus($conversationData, $data['status']);
                } else {
                    $this->mailHelper->messageQuote($conversationData);
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError("Unable to send quote message !!");
        }
    }
}
