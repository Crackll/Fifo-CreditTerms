<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ElasticSearch
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ElasticSearch\Controller\Adminhtml\Indexer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

use Magento\Framework\Exception;

class MassUpdateShedule extends AbstractIndexer
{
    /**
     * images list
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $collection = $this->_filter->getCollection($this->_indexerFactory->create()->getCollection());
        $indexUpdated = [];
        if ($collection->getSize() > 0) {
            foreach ($collection as $index) {
                $index->setMode(\Webkul\ElasticSearch\Api\Data\IndexerInterface::UPDATE_ON_SHEDULE);
                $index->save();
                $indexUpdated[] = $index->getType();
            }
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) were updated.', count($indexUpdated))
            );
        } else {
            $this->messageManager->addWarning(
                __('please select an index to update mode')
            );
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('elastic/indexer/index');
    }
}
