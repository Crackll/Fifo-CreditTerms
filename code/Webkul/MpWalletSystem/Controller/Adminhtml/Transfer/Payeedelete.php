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

namespace Webkul\MpWalletSystem\Controller\Adminhtml\Transfer;

use Webkul\MpWalletSystem\Controller\Adminhtml\Transfer as TransferController;
use Magento\Backend\App\Action;
use Webkul\MpWalletSystem;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Webkul MpWalletSystem Controller
 */
class Payeedelete extends TransferController
{
    /**
     * @var Filter
     */
    private $filter;
    
    /**
     * @var MpWalletSystem\Model\ResourceModel\WalletPayee\CollectionFactory
     */
    private $collectionFactory;

    /**
     * Initialize dependencies
     *
     * @param Action\Context                                                   $context
     * @param Filter                                                           $filter
     * @param \Webkul\MpWalletSystem\Helper\SplitPayments                      $splitPaymentHelper
     * @param MpWalletSystem\Model\ResourceModel\WalletPayee\CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        \Webkul\MpWalletSystem\Helper\SplitPayments $splitPaymentHelper,
        MpWalletSystem\Model\ResourceModel\WalletPayee\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->splitPaymentHelper = $splitPaymentHelper;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $payeedeleted = 0;
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        try {
            foreach ($collection as $item) {
                $this->splitPaymentHelper->deleteMethod($item);
                $payeedeleted++;
            }
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been deleted.', $payeedeleted)
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while Deleting the data.')
            );
        }
        return $resultRedirect->setPath('*/*/payeelist');
    }
}
