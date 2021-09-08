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

namespace Webkul\MpWholesale\Block\Adminhtml\Lead\Details\Edit\Tab;

class SentEmail extends \Magento\Backend\Block\Template
{
    /**
     * @var Webkul\MpWholesale\Model\LeadsFactory
     **/
    protected $leadsFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     **/
    protected $localeDate;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     **/
    protected $customerFactory;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Webkul\MpWholesale\Model\LeadsFactory $leadsFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $data
     **/
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\MpWholesale\Model\LeadsFactory $leadsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $data = []
    ) {
        $this->leadsFactory = $leadsFactory;
        $this->localeDate = $localeDate;
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $data);
    }

    /**
     * get sent mails to the seller
     * @return \Webkul\MpWholesale\Model\LeadsFactory $leadsFactory
     */
    public function getLeadDetails()
    {
        $leadId = $this->getRequest()->getParam('id');
        $leadModel = $this->leadsFactory->create()->load($leadId);
        $customerModel = $this->customerFactory->create()->load($leadModel->getSellerId());
        $createdTime = $this->localeDate->date($leadModel->getViewAt())
                                        ->format('Y-m-d H:i:s');
        $updateTime = $this->localeDate->date($leadModel->getRecentViewAt())
                                        ->format('Y-m-d H:i:s');
        return $data = [
            'Customer Name' => $customerModel->getFirstname().' '.$customerModel->getLastname(),
            'Customer Email' => $customerModel->getEmail(),
            'Product Name'  => $leadModel->getProductName(),
            'Total Viewed' => $leadModel->getViewCount(),
            'View At' => $createdTime,
            'Recent View At' => $updateTime
        ];
    }
}
