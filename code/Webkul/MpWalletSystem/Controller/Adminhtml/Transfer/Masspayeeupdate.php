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
class Masspayeeupdate extends TransferController
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
     * @param MpWalletSystem\Model\ResourceModel\WalletPayee\CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        MpWalletSystem\Model\ResourceModel\WalletPayee\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Update action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $resultRedirect = $this->resultRedirectFactory->create();
            $data = $this->getRequest()->getParams();
            $status = $data['entity_id'];
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $entityIds = $collection->getAllIds();
            if (!empty($entityIds)) {
                $coditionArr = [];
                foreach ($entityIds as $key => $id) {
                    $condition = "`entity_id`=".$id;
                    array_push($coditionArr, $condition);
                }
                $coditionData = implode(' OR ', $coditionArr);

                $creditRuleCollection = $this->collectionFactory->create();
                $creditRuleCollection->setTableRecords(
                    $coditionData,
                    ['status' => $status]
                );

                $this->messageManager->addSuccess(
                    __(
                        'A Total of %1 record(s) successfully updated.',
                        count($entityIds)
                    )
                );
            }
            return $resultRedirect->setPath('*/*/payeelist');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while Updating the data.')
            );
        }
        return $resultRedirect->setPath('*/*/payeelist');
    }
}
