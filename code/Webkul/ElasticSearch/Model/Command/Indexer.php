<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_ElasticSearch
 * @author Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\ElasticSearch\Model\Command;

use Magento\Store\Model\App\Emulation;

class Indexer
{

    /**
     * @var \Webkul\ElasticSearch\Helper\Data
     */
    protected $_helper;

    /**
     * @var array
     */
    protected $indexPool;

    /**
     * @var Webkul\ElasticSearch\Model\Adapter\ElasticAdapter
     */
    protected $_elasticAdapter;

    /**
     * @var \Webkul\ElasticSearch\Model\Indexer\Update
     */
    protected $_elasticUpdate;

    /**
     *
     * @param \Webkul\ElasticSearch\Helper\Data $helper
     * @param Emulation $emulate
     * @param \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter $elasticAdapter
     * @param \Webkul\ElasticSearch\Model\Indexer\Update\Mapping $elasticUpdate
     * @param array $indexPool
     */
    public function __construct(
        \Webkul\ElasticSearch\Helper\Data $helper,
        Emulation $emulate,
        \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter $elasticAdapter,
        \Webkul\ElasticSearch\Model\Indexer\Update\Mapping $elasticUpdate,
        array $indexPool = []
    ) {
        $this->_helper = $helper;
        $this->_emulate = $emulate;
        $this->_elasticAdapter = $elasticAdapter;
        $this->_elasticUpdate = $elasticUpdate;
        $this->indexPool = $indexPool;
    }

    /**
     * do reindex
     *
     * @return bool
     */
    public function doReindex($type, $storeId, $output = null)
    {
        try {
            $this->_helper->updateAttributesMapping();
            $indexCollection = $this->_helper->getIndexrs();
            if ($type !== 'all') {
                $indexCollection->addFieldToFilter("type", ["eq" => $type]);
            }
            $stores = $this->_helper->getAllStores();
            if ($storeId) {
                $stores = [];
                $stores[1] = $storeId;
            }
           
            $initialEnvironmentInfo = null;
            $this->consoleWrite("=====> creating|updating mapping for all indexes", $output);
            if ($this->createIndexMaping($output)) {
                $this->consoleWrite("<info>=====> mapping updated for all indexes</info>", $output);
            }
            
            foreach ($indexCollection as $indexModel) {
                $indexCount = 0;
                
                $indexType = $indexModel->getType();
                if (isset($this->indexPool[$indexType])) {
                   
                    foreach ($stores as $store) {

                        if (is_object($store)) {
                            $storeId = $store->getId();
                        } else {
                            $storeId = $store;
                        }
                        /**
                         * $initialEnvironmentInfo store emulation start
                         */
                        $initialEnvironmentInfo = $this->_emulate->startEnvironmentEmulation($storeId);
                        $this->_elasticAdapter->close(['index' => $this->_helper->getIndexName($indexModel->getIndex())]);
                        $this->_elasticUpdate->updateSettings($indexModel);
                        $this->_elasticUpdate->updateMapping($indexModel);
                        $this->_elasticAdapter->open(['index' => $this->_helper->getIndexName($indexModel->getIndex())]);
                        $indexes = $this->_elasticAdapter->getIndex(['index' => $this->_helper->getIndexName($indexModel->getIndex())]);
                        $this->consoleWrite("=====> creating index for type $indexType and store id $storeId", $output);
                        if ($indexes) {
                            $this->indexPool[$indexType]->doReindex(true);
                            $this->consoleWrite("<info>=====> index updated for type $indexType and store id $storeId </info>", $output);
                            $indexModel->setStatus(1);
                            $indexModel->save();
                        } else {
                            $this->consoleWrite("<error>=====> index mapping does not exist for type $indexType and store id $storeId </error>", $output);
                        }
                        
                        
                    }

                    $indexModel->setStatus(1);
                    $indexModel->save();
                } else {
                    $this->consoleWrite("<error>=====> invalid index type </error>", $output);
                }
                $this->consoleWrite("<info>=====> reindex complete </info>", $output);
            }
        } catch (\Exception $e) {
            $this->consoleWrite("<error>=====> ". $e->getMessage() ." </error>", $output);
        } finally {
            if ($initialEnvironmentInfo) {
                $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
            }
        }
    }

    /**
     * do remove index
     *
     * @return bool
     */
    public function doRemoveIndex($type, $cstoreId, $output = null)
    {
        try {
            $collection = $this->_helper->getIndexrs();
            if ($type != 'all') {
                $collection->addFieldToFilter("type", ["eq" => $type]);
            }
            $stores = $this->_helper->getAllStores();
            if ($cstoreId) {
                $stores = [];
                $stores[1] = $cstoreId;
            }
            
            $initialEnvironmentInfo = null;
            foreach ($collection as $model) {
                
                foreach ($stores as $store) {
                    if (!$cstoreId) {
                        $storeId = $store->getId();
                    } else {
                        $storeId = $cstoreId;
                    }
    
                    /**
                     * $initialEnvironmentInfo store emulation start
                     */
                    $initialEnvironmentInfo = $this->_emulate->startEnvironmentEmulation($storeId);
                    $indexes = $this->_elasticAdapter->getIndex(['index' => $this->_helper->getIndexName($model->getIndex())]);
                    if ($indexes) {
                        $this->consoleWrite("=====> deleting index with type ".$model->getType()." and store id $storeId", $output);
                        $this->_elasticAdapter->deleteIndex(['index' => $this->_helper->getIndexName($model->getIndex())]);
                        $this->consoleWrite("<info>=====> index deleted for type ".$model->getType()." and store id $storeId </info>", $output);
                    } else {
                        $this->consoleWrite("<error>=====> index does not exist for type ".$model->getType()." and store id $storeId </error>", $output);
                    }
                    $model->setStatus(0)->save();
                    
                    /**
                     * stop store emulation
                     */
                    $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
                }
            }
            $this->consoleWrite("<info>=====> successfully removed all the indexes </info>", $output);
        } catch (\Exception $e) {
            $this->consoleWrite("<error>=====> ".$e->getMessage()."</error>", $output);
            return false;
        } finally {
            if ($initialEnvironmentInfo) {
                $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
            }
        }
        return true;
    }

    /**
     * create indexes and mappings
     *
     * @return void
     */
    public function createIndexMaping($output)
    {
        try {
            $initialEnvironmentInfo = null;
            $this->_elasticAdapter->connect();
            $collection = $this->_helper->getIndexrs();
            $params = ['index' => []];
            $stores = $this->_helper->getAllStores();
            $defaultWebsite = 0;
            
            foreach ($collection as $index) {
                
                foreach ($stores as $store) {
                    $storeId = $store->getId();
                    
                    /**
                     * $initialEnvironmentInfo store emulation start
                     */
                    $initialEnvironmentInfo = $this->_emulate->startEnvironmentEmulation($storeId);
                    $indexes = $this->_elasticAdapter->getIndex(['index' => $this->_helper->getIndexName($index->getIndex())]);
                    
                    if (!$indexes) {
                        $res = $this->_elasticAdapter->createIndex($this->_helper->getIndexCreateData($index));
                        if ($res) {
                            $res = is_array($res)?$res:json_decode($res, true);
                        }
                        if (isset($res['error'])) {
                            $this->consoleWrite("<error>=====> ".json_encode($res, true)."<error>", $output);
                        }
                    }
                    //stop emulation
                    $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
                }
          
            }
        } catch (\Exception $e) {
            $this->consoleWrite("<error>=====> ".$e->getMessage()."<error>", $output);
            return false;
        } finally {
            if ($initialEnvironmentInfo) {
                $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
            }
        }
        return true;
    }

    /**
     * console write
     *
     * @return void
     */
    private function consoleWrite($message, $output)
    {
        if ($output) {
            $output->writeln($message);
        } else {
            $this->_helper->createCronLog($message);
        }
    }
}
