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

class Reindex extends AbstractIndexer
{
    /**
     * images list
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        try {
            $resultRedirect = $this->resultRedirectFactory->create();
            $indexId = $this->getRequest()->getParam("id");
    
            $initialEnvironmentInfo = null;
            if ($indexId) {
                $indexModel =  $this->_indexerFactory->create()->load($indexId);
                $stores = $this->_elasticHelper->getAllStores();
                $indexType = $indexModel->getType();
                if (isset($this->indexPool[$indexType])) {
                    
                    foreach ($stores as $store) {
                        $storeId = $store->getId();
                        /**
                         * $initialEnvironmentInfo store emulation start
                         */
                        $initialEnvironmentInfo = $this->_emulate->startEnvironmentEmulation($storeId);
                    
                        $indexes = $this->_elasticAdapter->getIndex(['index' => $this->_elasticHelper->getIndexName($indexModel->getIndex())]);
                    
                        if ($indexes) {
                            $this->indexPool[$indexType]->doReindex();
                        } else {
                            throw new \Exception("index mapping does not exist for type ".$indexModel->getType());
                        }
                        
                    }
                    $indexModel->setStatus(1);
                    $indexModel->save();
                } else {
                    throw new \Exception(__("invalid index type"));
                }
                $this->messageManager->addSuccess(__("reindex complete"));
            } else {
                $this->messageManager->addError(__("no index id provided to reindex"));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        } finally {
            if ($initialEnvironmentInfo) {
                $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
            }
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('elastic/indexer/index');
    }
}
