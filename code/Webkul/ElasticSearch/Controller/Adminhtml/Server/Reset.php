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
namespace Webkul\ElasticSearch\Controller\Adminhtml\Server;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

use Magento\Ui\Component\MassAction\Filter;

/**
 * Webkul ElasticSearch Reset Connection
 */
class Reset extends AbstractServer
{

    public function execute()
    {
        try {
            
            $collection = $this->_objectManager->create("Webkul\ElasticSearch\Model\ResourceModel\Indexer\CollectionFactory")->create();
            $stores = $this->_elasticHelper->getAllStores();
            $initialEnvironmentInfo = null;

            foreach ($collection as $model) {
                if ($model->getType() == 'pages') {
                    $indexes = $this->_elasticAdapter->getIndex(['index' => $model->getIndex()]);
                    if ($indexes) {
                        $this->_elasticAdapter->deleteIndex(['index' => $model->getIndex()]);
                        $model->setStatus(0)->save();
                    }
                } else {
                    foreach ($stores as $store) {
                        $storeId = $store->getId();
        
                        /**
                         * $initialEnvironmentInfo store emulation start
                         */
                        $initialEnvironmentInfo = $this->_emulate->startEnvironmentEmulation($storeId);
                        $indexes = $this->_elasticAdapter->getIndex(['index' => $this->_elasticHelper->getIndexName($model->getIndex())]);
                        if ($indexes) {
                            $this->_elasticAdapter->deleteIndex(['index' => $this->_elasticHelper->getIndexName($model->getIndex())]);
                        }
                        $model->setStatus(0)->save();
                        /**
                         * stop store emulation
                         */
                        $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
                    }
                }
            }
            return $this->getJsonResponse(['message' => __("All the mappings and data successfully removed from elastic,
            please save the configuration again to generate mappings and use CLI tool to index data again at elastic server."), 'success' => 1]);
        } catch (\Exception $e) {
            return $this->getJsonResponse(['message' => $e->getMessage(), 'error' => 1]);
        } finally {
            if ($initialEnvironmentInfo) {
                $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
            }
        }
    }
}
