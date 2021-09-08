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
namespace Webkul\MpWholesale\Plugin\Model;

use Webkul\MpWholesale\Model\WholeSaleUserFactory;

/**
 * Plugin to send mail to active wholesale users
 */
class AdminUser
{
    /**
     * @var WholeSaleUserFactory
     */
    private $wholeSaleUserFactory;

    /**
     * @var \Webkul\MpWholesale\Helper\Email
     */
    private $emailHelper;

    /**
     * @param WholeSaleUserFactory $wholeSaleUserFactory
     * @param \Webkul\MpWholesale\Helper\Email $emailHelper
     */
    public function __construct(
        WholeSaleUserFactory $wholeSaleUserFactory,
        \Webkul\MpWholesale\Helper\Email $emailHelper
    ) {
        $this->wholeSaleUserFactory = $wholeSaleUserFactory;
        $this->emailHelper = $emailHelper;
    }

    /**
     *
     * @param \Magento\User\Model\User $subject
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterSave(
        \Magento\User\Model\User $subject,
        \Magento\Framework\DataObject $object
    ) {
        $isActive = $object->getIsActive();
        $userId = $subject->getUserId();
        $wholeSaleUsers = $this->wholeSaleUserFactory->create()
                                ->getCollection()
                                ->addFieldToFilter('user_id', $userId);
        if ($wholeSaleUsers->getSize()) {
            foreach ($wholeSaleUsers as $wholeSaleUser) {
                if ($wholeSaleUser->getStatus() != $isActive) {
                    $wholeSaleUser->setStatus($isActive)->save();
                    $this->emailHelper->sendWholesalerApprovalMail($wholeSaleUser->getEntityId());
                }
            }
        }
        return $subject;
    }
}
