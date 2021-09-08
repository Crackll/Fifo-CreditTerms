<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Controller\Quotation;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Customer\Model\Session;
use Webkul\MpWholesale\Model\QuotesFactory;
use Webkul\MpWholesale\Helper\Data;

/**
 * Webkul MpWholesale Quotation Save Controller.
 */
class Save extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var FormKeyValidator
     */
    protected $formKeyValidator;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;
    /**
     * @var QuotesFactory
     */
    protected $quotesFactory;
    /**
     * @var \Webkul\MpWholesale\Helper\Email
     */
    protected $emailHelper;

    /**
     * @var Data
     */
    protected $wholesaleHelper;

    /**
     * @param Context $context
     * @param FormKeyValidator $formKeyValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param QuotesFactory $quotesFactory
     * @param \Webkul\MpWholesale\Helper\Email $emailHelper
     * @param Session $customerSession
     * @param Data    $wholesaleHelper
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        Context $context,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        QuotesFactory $quotesFactory,
        \Webkul\MpWholesale\Helper\Email $emailHelper,
        Session $customerSession,
        Data $wholesaleHelper,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->date = $date;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->quotesFactory = $quotesFactory;
        $this->customerSession = $customerSession;
        $this->emailHelper = $emailHelper;
        $this->wholesaleHelper = $wholesaleHelper;
        $this->escaper    =   $escaper;
        parent::__construct($context);
    }

    /**
     * Ask Query to seller action.
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $response = 0;
        if ($this->getRequest()->isPost() && $this->wholesaleHelper->getModuleStatus()) {
            $data = $this->getRequest()->getParams();
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->getResponse()->setBody($response);
            }
            if ($this->validateData($data)) {
                $mpHelper = $this->marketplaceHelper;
                $data['seller_id'] = $mpHelper->getCustomerId();
                $data['status'] = \Webkul\MpWholesale\Model\Quotes::STATUS_UNAPPROVED;
                $data['created_at'] = $this->date->gmtDate();
                $data['quote_msg'] = $this->escaper->escapeHtml($data['quote_msg']);
                $quoteId = $this->saveQuote($data);
                if ($quoteId) {
                    $response = 1;
                    $data['quote_id'] = $quoteId;
                    $this->emailHelper->newQuote($data);
                }
            }
        }
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($response);
    }

    /**
     * Save WholeSale Product Quote
     *
     * @param array $wholeData
     * @return integer
     */
    private function saveQuote($wholeData)
    {
        $quoteModel = $this->quotesFactory->create()
                    ->setData($wholeData)
                    ->save();
        $quoteId = $quoteModel->getId();
        return $quoteId;
    }

    /**
     * Check the validation of the Quote array
     *
     * @param array $wholeData
     * @return boolean
     */
    public function validateData($wholeData)
    {
        $flag = true;
        $quoteQty = $wholeData['quote_qty'];
        $quotePrice = $wholeData['quote_price'];
        $quoteMsg = $wholeData['quote_msg'];
        if ($quoteQty == ""
         || !preg_match("/^([0-9])+?[0-9.]*$/", $quoteQty)
         || $quoteMsg == ""
         || $quotePrice == ""
         || !preg_match("/^([0-9])+?[0-9.]*$/", $quotePrice)) {
            $flag = false;
        }
        return $flag;
    }
}
