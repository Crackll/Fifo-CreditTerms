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
namespace Webkul\MpWalletSystem\Ui\Component\DataProvider;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\MpWalletSystem\Model\Wallettransaction;

class Document extends \Magento\Framework\View\Element\UiComponent\DataProvider\Document
{
    /**
     * @var string
     */
    private static $referenceAttributeCode = 'increment_id';

    /**
     * @var string
     */
    private static $statusAttributeCode = 'status';

    /**
     * @var walletHelper
     */
    private $walletHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Document constructor.
     *
     * @param AttributeValueFactory $attributeValueFactory
     * @param walletHelper          $walletHelper
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface  $scopeConfig
     */
    public function __construct(
        AttributeValueFactory $attributeValueFactory,
        \Webkul\MpWalletSystem\Helper\Data $walletHelper,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($attributeValueFactory);
        $this->walletHelper = $walletHelper;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @inheritdoc
     */
    public function getCustomAttribute($attributeCode)
    {
        switch ($attributeCode) {
            case self::$referenceAttributeCode:
                $this->setReferenceValue();
                break;
            case self::$statusAttributeCode:
                $this->setStatusValue();
                break;
        }
        return parent::getCustomAttribute($attributeCode);
    }

    /**
     * Update customer gender value
     * Method set gender label instead of id value
     *
     * @return void
     */
    private function setReferenceValue()
    {
        $value = $this->getData(self::$referenceAttributeCode);
        $senderType = $this->getData('sender_type');
        $actionType = $this->getData('action');
        $prefix = $this->walletHelper->getTransactionPrefix(
            $senderType,
            $actionType
        );
        $this->setCustomAttribute(self::$referenceAttributeCode, $prefix);
    }

    /**
     * Set Status Value function
     *
     * @return void
     */
    private function setStatusValue()
    {
        $value = $this->getData(self::$statusAttributeCode);
        $valueText = ($value == Wallettransaction::WALLET_TRANS_STATE_APPROVE) ?
        __('Approved')->__toString() : (($value == Wallettransaction::WALLET_TRANS_STATE_CANCEL)
         ? __('Cancelled')->__toString() : __('Pending')->__toString());
        $this->setCustomAttribute(self::$statusAttributeCode, $valueText);
    }
}
