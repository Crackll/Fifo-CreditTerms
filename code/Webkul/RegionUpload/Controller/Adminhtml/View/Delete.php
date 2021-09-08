<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RegionUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\RegionUpload\Controller\Adminhtml\View;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\RegionUpload\Api\RegionRepositoryInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\RegionUpload\Model\ResourceModel\Region\CollectionFactory;

class Delete extends Action implements HttpPostActionInterface
{

    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RegionFactory $regionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        PageFactory $resultPageFactory,
        RegionRepositoryInterface $regionRepository
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->regionRepository = $regionRepository;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Webkul_RegionUpload::region_upload");
    }

    public function execute()
    {
        $param = $this->_request->getParam('selected');
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $countRecord = $collection->getSize();
            foreach ($collection as $item) {
                $item->delete();
            }
            $this->messageManager->addSuccess(
                __(
                    'A total of %1 record(s) have been deleted.',
                    $countRecord
                )
            );
        } catch (\Exception $e) {
            $this->messageManager->addError(__("The Regions can't be Deleted"));
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/index');
    }
}
