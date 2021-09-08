<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplaceProductLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MarketplaceProductLabels\Model\ResourceModel\Label\CollectionFactory;
use Webkul\MarketplaceProductLabels\Helper\Data;
use Magento\Customer\Model\CustomerFactory;
use Webkul\MarketplaceProductLabels\Helper\Email;

class MassDisApprove extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var Webkul\MarketplaceProductLabels\Model\ResourceModel\Label\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $helper;

    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var Webkul\MarketplaceProductLabels\Helper\Email
     */
    protected $email;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        Data $helper,
        CustomerFactory $customerFactory,
        Email $email
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        $this->customerFactory = $customerFactory;
        $this->email = $email;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MarketplaceProductLabels::seller_label_grid');
    }

    /**
     * Label MassDisApprove Action
     *
     * @return void
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create()
                                                ->addFieldToFilter('status', ['in' => [
                                                    \Webkul\MarketplaceProductLabels\Model\Label::STATUS_ENABLE,
                                                    \Webkul\MarketplaceProductLabels\Model\Label::STATUS_PENDING
                                                ]]));

        $adminStoreEmail = $this->helper->getAdminEmailId();
        $adminEmail = $adminStoreEmail ? $adminStoreEmail : $this->helper->getDefaultTransEmailId();
        $adminUsername = $this->helper->getAdminName();

        $count = 0;
        foreach ($collection as $item) {
            $sellerId = $item->getSellerId();
            if ($sellerId != 0) {
                $seller = $this->loadSeller($sellerId);
                $senderInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail,
                ];
                $receiverInfo = [
                    'name' => $seller->getName(),
                    'email' => $seller->getEmail(),
                ];
                $storeId = $seller->getStoreId();

                $emailTemplateVariables = [];
                $emailTemplateVariables['myvar1'] = $item->getLabelName();
                $emailTemplateVariables['myvar2'] = $seller->getName();

                $this->email->sendLabelDisApprovalMail(
                    $emailTemplateVariables,
                    $senderInfo,
                    $receiverInfo,
                    $storeId
                );
            }
            $this->disApproveItem($item);
            $count++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', $count));
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Set Label Status
     *
     * @param [type] $item
     * @return void
     */
    public function disApproveItem($item)
    {
        $item->setStatus(\Webkul\MarketplaceProductLabels\Model\Label::STATUS_DISABLE);
        $item->save();
    }

    /**
     *  Load Customer
     *
     * @param [type] $id
     * @return label
     */
    public function loadSeller($id)
    {
        $seller = $this->customerFactory->create()->load($id);
        return $seller;
    }
}
