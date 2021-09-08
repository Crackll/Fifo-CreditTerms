<?php
/**
 * Webkul MpWholesale Email Helper
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Webkul\MpWholesale\Helper\Data as MpWholesaleHelperData;
use Magento\User\Model\UserFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * MpWholesale Email helper
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_PATH_NEW_WHOLESALER_CREATE  = 'mpwholsale/email/new_account_notification';
    const XML_PATH_NEW_QUOTE    = 'mpwholsale/email/new_quote';
    const XML_PATH_MSG_QUOTE    = 'mpwholsale/email/quote_message';
    const XML_PATH_CUSTOM_EMAIL = 'mpwholsale/email/custom_email';
    const XML_PATH_QUOTE_STATUS = 'mpwholsale/email/quote_status';
    const XML_PATH_NEW_PRODUCT  = 'mpwholsale/email/new_product';
    const XML_PATH_PRODUCT_APPROVAL = 'mpwholsale/email/product_approve';
    const XML_PATH_PRODUCT_UNAPPROVAL = 'mpwholsale/email/product_unapprove';
    const XML_PATH_WHOLESALER_APPROVE = 'mpwholsale/email/wholesaler_approve';
    const XML_PATH_WHOLESALER_INACTIVE = 'mpwholsale/email/wholesaler_inactive';
    const XML_PATH_EMAIL_BECOME_WHOLESALER = 'mpwholsale/email/become_wholesaler_request';

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var MpWholesaleHelperData
     */

    protected $mpWholesaleHelperData;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $backendUrlModel;

    /**
     * @var string
     */

    protected $_tempId;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $inlineTranslation
     * @param TransportBuilder $transportBuilder
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Webkul\MpWholesale\Model\ProductFactory $wsProductFactory
     * @param \Webkul\MpWholesale\Model\WholeSaleUserFactory $wholeSaleUserFactory
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Backend\Model\Url $backendUrlModel
     * @param MpWholesaleHelperData $mpWholesaleHelperData
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        TransportBuilder $transportBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\MpWholesale\Model\ProductFactory $wsProductFactory,
        \Webkul\MpWholesale\Model\WholeSaleUserFactory $wholeSaleUserFactory,
        \Magento\Framework\Escaper $escaper,
        \Magento\Backend\Model\Url $backendUrlModel,
        MpWholesaleHelperData $mpWholesaleHelperData
    ) {
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->mpWholesaleHelperData = $mpWholesaleHelperData;
        $this->messageManager = $messageManager;
        $this->productFactory = $productFactory;
        $this->wsProductFactory = $wsProductFactory;
        $this->wholeSaleUserFactory = $wholeSaleUserFactory;
        $this->escaper = $escaper;
        $this->backendUrlModel = $backendUrlModel;
        parent::__construct($context);
    }

    /**
     * Return store configuration value
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Return template id
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * [generateTemplate description]
     * @param  $emailTemplateVariables,
     * @param  $senderInfo,
     * @param  $receiverInfo
     * @return void
     */
    public function generateTemplate(
        $emailTemplateVariables,
        $senderInfo,
        $receiverInfo
    ) {
        $template =  $this->transportBuilder->setTemplateIdentifier($this->_tempId)->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => 0]
        )->setTemplateVars($emailTemplateVariables)->setFrom($senderInfo)
                            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }

    /**
     * [sendWholesalerCreateNotification description]
     * @param  $data,
     * @return void
     */
    public function sendWholesalerCreateNotification($data)
    {
        $emailTemplateVariables['username'] = $data['username'];
        $emailTemplateVariables['name']     = $data['firstname']." ".$data['lastname'];
        $emailTemplateVariables['password'] = $data['password'];

        $senderInfo = [
            'name' => $this->mpWholesaleHelperData->getAdminName(),
            'email' => $this->mpWholesaleHelperData->getAdminEmail(),
        ];
        $receiverInfo = [
            'name' => $data['firstname']." ".$data['lastname'],
            'email' => $data['email'],
        ];
        if (isset($data['is_active']) && $data['is_active'] == 0) {
            $emailTemplateVariables['approval_msg'] = __("Please wait for the admin approval.");
        }
        $this->_tempId = $this->getTemplateId(self::XML_PATH_NEW_WHOLESALER_CREATE);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Not able to send the email.')
            );
        }
        $this->inlineTranslation->resume();
    }

    /**
     * [sendBecomeWholesaleNotification description].
     *
     * @param Mixed $data
     */
    public function sendBecomeWholesaleNotification($data)
    {
        $name = $data['firstname']." ".$data['lastname'];
        $receiverInfo = [
          'name' => $this->mpWholesaleHelperData->getAdminName(),
          'email' => $this->mpWholesaleHelperData->getAdminEmail(),
        ];
        $senderInfo = [
          'name' => $name,
          'email' => $data['email'],
        ];
        $emailTemplateVariables['myvar1'] = $name;
        $emailTemplateVariables['myvar2'] = $this->backendUrlModel->getUrl(
            'mpwholesale/user/index'
        );
        $emailTemplateVariables['myvar3'] = $this->mpWholesaleHelperData->getAdminName();
        $this->_tempId = $this->getTemplateId(self::XML_PATH_EMAIL_BECOME_WHOLESALER);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            throw new LocalizedException(
                __('Not able to send the email.')
            );
        }
        $this->inlineTranslation->resume();
    }
    /**
     * Send New Quote Mail
     *
     * @param array $quoteData
     * @return mixed
     */
    public function newQuote($quoteData)
    {
        $helper = $this->mpWholesaleHelperData;
        $seller = $helper->getCustomerData($quoteData['seller_id']);
        $senderInfo = [
            'name' => $seller->getName(),
            'email' => $seller->getEmail(),
        ];
        $wholeSaler = $helper->getWholesalerData($quoteData['wholesaler_id']);
        $receiverInfo = [
            'name' => $wholeSaler->getFirstname(),
            'email' => $wholeSaler->getEmail(),
        ];
        $allowedTags = null;
        $emailTemplateVariables['receiver_name'] = $wholeSaler->getFirstname();
        $emailTemplateVariables['title'] = __('New Quote has been created. Please check.');
        $emailTemplateVariables['quote_id'] = $quoteData['quote_id'];
        $emailTemplateVariables['product_name'] = $quoteData['product_name'];
        $emailTemplateVariables['quote_qty'] = $quoteData['quote_qty'];
        $emailTemplateVariables['quote_price'] = $quoteData['quote_price'];
        $emailTemplateVariables['quote_description'] = $this->escaper->escapeHtml(
            $quoteData['quote_msg'],
            $allowedTags
        );
        $this->_tempId = $this->getTemplateId(self::XML_PATH_NEW_QUOTE);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError('Not able to send the email.');
        }
        $this->inlineTranslation->resume();
    }

    /**
     * Set Quote Message Status
     *
     * @param array $data
     * @param string $status
     * @return mixed
     */
    public function messageQuoteStatus($data, $status)
    {
        $helper = $this->mpWholesaleHelperData;
        if ($data['msg_from'] == 'seller') {
            $seller = $helper->getCustomerData($data['sender_id']);
            $senderInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
            $wholeSaler = $helper->getWholesalerData($data['receiver_id']);
            $receiverInfo = [
                'name' => $wholeSaler->getFirstname().' '.$wholeSaler->getLastname(),
                'email' => $wholeSaler->getEmail(),
            ];
        } else {
            $wholeSaler = $helper->getWholesalerData($data['sender_id']);
            $senderInfo = [
                'name' => $wholeSaler->getFirstname().' '.$wholeSaler->getLastname(),
                'email' => $wholeSaler->getEmail(),
            ];
            $seller = $helper->getCustomerData($data['receiver_id']);
            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
        }
        if ($status == \Webkul\MpWholesale\Model\Quotes::STATUS_UNAPPROVED) {
            $status = 'UnApproved';
        } elseif ($status == \Webkul\MpWholesale\Model\Quotes::STATUS_APPROVED) {
            $status = 'Approved';
        } else {
            $status = 'Declined';
        }
        $emailTemplateVariables['receiver_name'] = $receiverInfo['name'];
        $emailTemplateVariables['title'] = __(
            'Status has been changed for quote id : %1',
            $data['quote_id']
        );
        $allowedTags = null;
        $emailTemplateVariables['new_message'] = $this->escaper->escapeHtml($data['conversation'], $allowedTags);
        $emailTemplateVariables['new_status'] = $status;
        $this->_tempId = $this->getTemplateId(self::XML_PATH_QUOTE_STATUS);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError('Not able to send the email.');
        }
        $this->inlineTranslation->resume();
    }

    /**
     * Quote Message Set
     *
     * @param array $data
     * @return mixed
     */
    public function messageQuote($data)
    {
        $helper = $this->mpWholesaleHelperData;
        if ($data['msg_from'] == 'seller') {
            $seller = $helper->getCustomerData($data['sender_id']);
            $senderInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
            $wholeSaler = $helper->getWholesalerData($data['receiver_id']);
            $receiverInfo = [
                'name' => $wholeSaler->getFirstname().' '.$wholeSaler->getLastname(),
                'email' => $wholeSaler->getEmail(),
            ];
        } else {
            $wholeSaler = $helper->getWholesalerData($data['sender_id']);
            $senderInfo = [
                'name' => $wholeSaler->getFirstname().' '.$wholeSaler->getLastname(),
                'email' => $wholeSaler->getEmail(),
            ];
            $seller = $helper->getCustomerData($data['receiver_id']);
            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
        }
        $emailTemplateVariables['receiver_name'] = $receiverInfo['name'];
        if ($data['msg_from'] == 'seller') {
            $emailTemplateVariables['title'] = __(
                'New Message has been appended to quote id : %1',
                $data['quote_id']
            );
        } else {
            $emailTemplateVariables['title'] = __(
                'Wholesaler replied to your quotation.
                quote id : %1.',
                $data['quote_id']
            );
            $emailTemplateVariables['title_msg'] = __('Please visit the website to know more details.');
        }
        $allowedTags = null;
        $emailTemplateVariables['new_message'] = $this->escaper->escapeHtml($data['conversation'], $allowedTags);
        $this->_tempId = $this->getTemplateId(self::XML_PATH_MSG_QUOTE);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError('Not able to send the email.');
        }
        $this->inlineTranslation->resume();
    }

    /**
     * Send Mail To Seller
     *
     * @param array $data
     * @return mixed
     */
    public function sendCustomMail($data)
    {
        $helper = $this->mpWholesaleHelperData;
        $senderInfo = [
            'name' => $data['adminName'],
            'email' => $data['adminEmail'],
        ];
        $receiverInfo = [
            'name' => $data['seller_name'],
            'email' => $data['seller_email'],
        ];
        $emailTemplateVariables['sellername'] = $receiverInfo['name'];
        $allowedTags = null;
        $emailTemplateVariables['message'] = $this->escaper->escapeHtml($data['mailBody'], $allowedTags);
        $this->_tempId = $this->getTemplateId(self::XML_PATH_CUSTOM_EMAIL);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError('Not able to send the email.');
        }
        $this->inlineTranslation->resume();
    }

    /**
     * Send New Product Mail
     *
     * @param array $data
     * @return mixed
     */
    public function sendNewProductMail($data)
    {
        $helper = $this->mpWholesaleHelperData;
        $wholeSaler = $helper->getWholesalerData($data['user_id']);
        $senderInfo = [
            'name' => $wholeSaler->getFirstname().' '.$wholeSaler->getLastname(),
            'email' => $wholeSaler->getEmail(),
        ];
        $receiverInfo = [
            'name' => $this->mpWholesaleHelperData->getAdminName(),
            'email' => $this->mpWholesaleHelperData->getAdminEmail(),
        ];
        $productName = $this->productFactory->create()->load($data['product_id'])->getName();
        $emailTemplateVariables['myvar3'] = $receiverInfo['name'];
        $emailTemplateVariables['myvar1'] = $productName;
        $emailTemplateVariables['myvar2'] = $senderInfo['name'];
        $emailTemplateVariables['myvar4'] = __(
            'I would like to inform you that recently I have added a new wholesale product.'
        );
        $this->_tempId = $this->getTemplateId(self::XML_PATH_NEW_PRODUCT);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError('Not able to send the email.');
        }
        $this->inlineTranslation->resume();
    }

    public function sendProductApprovalMail($id)
    {
        $wholeSaleProduct = $this->wsProductFactory->create()->load($id);
        $helper = $this->mpWholesaleHelperData;
        $wholeSaler = $helper->getWholesalerData($wholeSaleProduct->getUserId());
        $receiverInfo = [
            'name' => $wholeSaler->getFirstname().' '.$wholeSaler->getLastname(),
            'email' => $wholeSaler->getEmail(),
        ];
        $senderInfo = [
            'name' => $this->mpWholesaleHelperData->getAdminName(),
            'email' => $this->mpWholesaleHelperData->getAdminEmail(),
        ];
        $productName = $this->productFactory->create()->load($wholeSaleProduct->getProductId())->getName();
        $emailTemplateVariables['myvar2'] = $productName;
        $emailTemplateVariables['myvar1'] = $receiverInfo['name'];
        $this->_tempId = $this->getTemplateId(self::XML_PATH_PRODUCT_APPROVAL);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError('Not able to send the email.');
        }
        $this->inlineTranslation->resume();
    }

    /**
     * Send product Unapproval Mail
     *
     * @param integer $id
     * @return mixed
     */
    public function sendProductUnapprovalMail($id)
    {
        $wholeSaleProduct = $this->wsProductFactory->create()->load($id);
        $helper = $this->mpWholesaleHelperData;
        $wholeSaler = $helper->getWholesalerData($wholeSaleProduct->getUserId());
        $receiverInfo = [
            'name' => $wholeSaler->getFirstname().' '.$wholeSaler->getLastname(),
            'email' => $wholeSaler->getEmail(),
        ];
        $senderInfo = [
            'name' => $this->mpWholesaleHelperData->getAdminName(),
            'email' => $this->mpWholesaleHelperData->getAdminEmail(),
        ];
        $productName = $this->productFactory->create()->load($wholeSaleProduct->getProductId())->getName();
        $emailTemplateVariables['myvar2'] = $productName;
        $emailTemplateVariables['myvar1'] = $receiverInfo['name'];
        $this->_tempId = $this->getTemplateId(self::XML_PATH_PRODUCT_UNAPPROVAL);
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError('Not able to send the email.');
        }
        $this->inlineTranslation->resume();
    }

    /**
     * Send WholeSaler Approval Mail
     *
     * @param integer $id
     * @return mixed
     */
    public function sendWholesalerApprovalMail($id)
    {
        $wholeSaleUser = $this->wholeSaleUserFactory->create()->load($id);
        $helper = $this->mpWholesaleHelperData;
        $wholeSaler = $helper->getWholesalerData($wholeSaleUser->getUserId());
        $receiverInfo = [
            'name' => $wholeSaler->getFirstname().' '.$wholeSaler->getLastname(),
            'email' => $wholeSaler->getEmail(),
        ];
        $senderInfo = [
            'name' => $this->mpWholesaleHelperData->getAdminName(),
            'email' => $this->mpWholesaleHelperData->getAdminEmail(),
        ];
        $emailTemplateVariables['myvar1'] = $receiverInfo['name'];
        $this->_tempId = $this->getTemplateId(self::XML_PATH_WHOLESALER_INACTIVE);
        if ($wholeSaleUser->getStatus()) {
            $this->_tempId = $this->getTemplateId(self::XML_PATH_WHOLESALER_APPROVE);
        }
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError('Not able to send the email.');
        }
        $this->inlineTranslation->resume();
    }
}
