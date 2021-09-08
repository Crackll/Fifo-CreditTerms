<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerApproval
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\CustomerApproval\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;

class EmailNotification
{
   
    /**
     * @param \Webkul\CustomerApproval\Helper\Data               $helper
     */
    public function __construct(
        \Webkul\CustomerApproval\Helper\Data $helper
    ) {
        $this->customerApprovalHelper = $helper;
    }

    /**
     * Send email with new account related information
     *
     * @param \Magento\Customer\Model\EmailNotification           $subjet
     * @param callable $proceed
     * @param CustomerInterface $customer
     * @param string $type
     * @param string $backUrl
     * @param int $storeId
     * @param string $sendemailStoreId
     * @return void
     * @throws LocalizedException
     */
    
    public function aroundNewAccount(
        \Magento\Customer\Model\EmailNotification $subjet,
        callable $proceed,
        CustomerInterface $customer,
        $type,
        $backUrl,
        $storeId,
        $sendemailStoreId = null
    ) {
        $isAuto = $this->customerApprovalHelper->isAutoApproval(); //Check Auto approval
        if ($isAuto != 1) {
            if ($this->customerApprovalHelper->getConfirmationRequired($customer->getWebsiteId())) {
                $proceed(
                    $customer,
                    $type,
                    $backUrl,
                    $storeId,
                    $sendemailStoreId
                );
            } else {
                return;
            }
        } else {
            $val =  $customer->getCustomAttribute("wk_customer_approval")->getValue();
            if ($val == 1) {
                return $proceed(
                    $customer,
                    $type,
                    $backUrl,
                    $storeId,
                    $sendemailStoreId
                );
            } else {
                return;
            }
        }
    }
}
