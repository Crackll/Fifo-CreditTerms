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
namespace Webkul\ElasticSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Store\Model\App\Emulation;

class ConfigChange implements ObserverInterface
{

    /**
     * Cron string path
     */
    const CRON_STRING_PATH = 'crontab/default/jobs/elasticsearch/cron_settings/cron_expr';

    /**
     * @var Webkul\ElasticSearch\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepo;

    /**
     * @var Webkul\ElasticSearch\Model\Adapter
     */
    protected $_elasticAdapter;

    /**
     * @var \Magento\Framework\App\Config\ValueFactory
     */
    protected $_configValueFactory;

    /**
     * store emulater
     *
     * @var Emulation
     */
    protected $_emulate;

    /**
     * @var \Webkul\ElasticSearch\Model\Indexer\Update
     */
    protected $_elasticUpdate;

    public function __construct(
        \Webkul\ElasticSearch\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter $elasticAdapter,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Webkul\ElasticSearch\Model\Indexer\Update\Mapping $elasticUpdate,
        Emulation $emulate
    ) {
        $this->_helper = $helper;
        $this->request = $request;
        $this->_elasticAdapter = $elasticAdapter;
        $this->_configValueFactory = $configValueFactory;
        $this->_elasticUpdate = $elasticUpdate;
        $this->_emulate = $emulate;
    }
    
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->_helper->canUseElastic()) {
            return $this;
        }

        try {
            $initialEnvironmentInfo = null;
            $requestParams = $this->request->getParams();

            $this->_elasticAdapter->connect();
            $collection = $this->_helper->getIndexrs();
            $params = ['index' => []];
            $stores = $this->_helper->getAllStores();
            $defaultWebsite = $observer->getWebsite()?$observer->getWebsite():0;
            
            foreach ($collection as $index) {
           
                foreach ($stores as $store) {
                    $storeId = $store->getId();
                    
                        /**
                         * $initialEnvironmentInfo store emulation start
                         */
                    $initialEnvironmentInfo = $this->_emulate->startEnvironmentEmulation($storeId);
                    
                    if ($defaultWebsite == 0) {
                        $indexes = $this->_elasticAdapter->getIndex(['index' => $this->_helper->getIndexName($index->getIndex())]);
                        
                        if (!$indexes) {
                            $res = $this->_elasticAdapter->createIndex($this->_helper->getIndexCreateData($index));
                            if (is_string($res)) {
                                $res = json_decode($res, true);
                                if (isset($res['error'])) {
                                    $this->_helper->createLog($res);
                                    throw new \Exception(__("something went wrong while creating the mapping, please check logs"));
                                }
                            }
                        } else {
                            if (isset($requestParams['groups']['search_settings'])) {
                                /**
                                 * update mapping and settings
                                 */
                                $this->_elasticAdapter->close(['index' => $this->_helper->getIndexName($index->getIndex())]);
                                $this->_elasticUpdate->updateSettings($index);
                                $this->_elasticUpdate->updateMapping($index);
                                $this->_elasticAdapter->open(['index' => $this->_helper->getIndexName($index->getIndex())]);
                            }
                        }
                    } elseif ($defaultWebsite == $store->getWebsiteId()) {
                        $indexes = $this->_elasticAdapter->getIndex(['index' => $this->_helper->getIndexName($index->getIndex())]);
                    
                        if (!$indexes) {
                            $res = $this->_elasticAdapter->createIndex($this->_helper->getIndexCreateData($index));
                            if (is_string($res)) {
                                $res = json_decode($res, true);
                                if (isset($res['error'])) {
                                    $this->_helper->createLog($res);
                                    throw new \Exception(__("something went wrong while creating the mapping, please check logs"));
                                }
                            }
                        } else {
                            if (isset($requestParams['groups']['search_settings'])) {
                                /**
                                 * update mapping and settings
                                 */
                                $this->_elasticAdapter->close(['index' => $this->_helper->getIndexName($index->getIndex())]);
                                $this->_elasticUpdate->updateSettings($index);
                                $this->_elasticUpdate->updateMapping($index);
                                $this->_elasticAdapter->open(['index' => $this->_helper->getIndexName($index->getIndex())]);
                            }
                        }
                    }

                    //stop emulation
                    $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
                }
                   
            }
        } catch (\Exception $e) {
            $this->_helper->createExceptionLog($e);
            throw new \Exception($e->getMessage());
        } finally {
            if ($initialEnvironmentInfo) {
                $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
            }
        }
    }

}
