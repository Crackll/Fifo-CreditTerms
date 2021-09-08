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

namespace Webkul\MpWalletSystem\Block;

use Webkul\MpWalletSystem\Model\ResourceModel\Walletrecord;

/**
 * Webkul MpWalletSystem Block
 */
class VerificationCode extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\MpWalletSystem\Model\ResourceModel\Walletrecord
     */
    private $walletrecordModel;
    
    /**
     * @var Webkul\MpWalletSystem\Helper\Data
     */
    private $walletHelper;
    
    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    private $pricingHelper;
    
    /**
     * Initialize dependencies
     *
     * @param MagentoFrameworkViewElementTemplateContext $context
     * @param WalletrecordCollectionFactory              $walletrecordModel
     * @param WebkulWalletsystemHelperData               $walletHelper
     * @param MagentoFrameworkPricingHelperData          $pricingHelper
     * @param array                                      $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Walletrecord\CollectionFactory $walletrecordModel,
        \Webkul\MpWalletSystem\Helper\Data $walletHelper,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->walletrecordModel = $walletrecordModel;
        $this->_encryptor = $encryptor;
        $this->walletHelper = $walletHelper;
        $this->pricingHelper = $pricingHelper;
    }
    
    /**
     * Use to get current url.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        // Give the current url of recently viewed page
        return $this->_urlBuilder->getCurrentUrl();
    }
    
    /**
     * GetIsSecure check is secure or not
     *
     * @return boolean
     */
    public function getIsSecure()
    {
        return $this->getRequest()->isSecure();
    }

    /**
     * Get transfered parameterd passed in request
     *
     * @return array
     */
    public function getTransferParameters()
    {
        $params = [];
        $getEncodedParamData = $this->getRequest()->getParam('parameter');
        $paramsJson = $this->_encryptor->decrypt(urldecode($getEncodedParamData));
        if ($paramsJson) {
            $params = json_decode($paramsJson, true);
        }
        return $params;
    }

    /**
     * Get remaining total of a customer
     *
     * @param int $customerId
     * @return void
     */
    public function getWalletRemainingTotal($customerId)
    {
        $remainingAmount = 0;
        $walletRecordCollection = $this->walletrecordModel->create()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        if ($walletRecordCollection->getSize()) {
            foreach ($walletRecordCollection as $record) {
                $remainingAmount = $record->getRemainingAmount();
            }
        }
        return $this->pricingHelper
            ->currency($remainingAmount, true, false);
    }
}
