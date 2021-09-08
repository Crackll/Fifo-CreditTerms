<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCategory
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCategory\Controller\Category;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpSellerCategory\Helper\Data as HelperData;
use Webkul\MpSellerCategory\Model\ResourceModel\Category\CollectionFactory;

class MassEnable extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var HelperData
     */
    protected $_helper;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param HelperData $helper
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        HelperData $helper,
        CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->_helper = $helper;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        try {
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $countRecord = $collection->getSize();
            $collection->enableCategories();
            $this->_helper->clearCache();
            $this->messageManager->addSuccess(
                __(
                    'A total of %1 record(s) have been updated.',
                    $countRecord
                )
            );

            /** @var \Magento\Framework\Controller\Result\RedirectFactory $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $this->resultRedirectFactory->create()->setPath(
                'mpsellercategory/category/manage',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());

            return $this->resultRedirectFactory->create()->setPath(
                'mpsellercategory/category/manage',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
