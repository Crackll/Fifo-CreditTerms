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

namespace Webkul\ElasticSearch\Model\Adapter;

use Elasticsearch;

class ElasticAdapter
{

    public static $connection;

    /**
     * @var Webkul\ElasticSearch\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Webkul\ElasticSearch\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * connect to elastic server
     *
     * @throws Exception
     * @return bool
     */
    public function connect()
    {
        if (!self::$connection) {
            if ($this->validateConfig()) {
                $clientBuilder = \Elasticsearch\ClientBuilder::create();
                $clientBuilder->setHosts([$this->_helper->getHost().':'.$this->_helper->getPort()]);
                $client = $clientBuilder->build();
                $status = $client->info();
                if (!is_array($status)) {
                    throw new \Exception(__("invalid configuration"));
                }
                self::$connection = $client;
                return $client;
            } else {
                throw new \Exception(__("invalid configuration"));
            }
        } else {
            return self::$connection;
        }
    }

    /**
     * connect to elastic server
     *
     * @throws Exception
     * @return bool
     */
    public function reset()
    {
        if (self::$connection) {
            self::$connection = null;
        }
        $this->_helper->resetConfig();
        return true;
    }



    /**
     * validate configuration
     *
     * @return bool
     */
    private function validateConfig()
    {
        $host = $this->_helper->getHost();
        $port = $this->_helper->getPort();
        if ($host && $port) {
            return true;
        } else {
            return false;
        }

        return false;
    }

    /**
     * create index on elastic
     *
     * @return void
     */
    public function createIndex($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        try {
            return self::$connection->indices()->create($params);
        } catch (\Exception $e) {
            return  $e->getMessage();
        }
    }

    /**
     * get index settings
     *
     * @return void
     */
    public function getIndex($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        try {
            return self::$connection->indices()->getSettings($params);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * delete index
     *
     * @return void
     */
    public function deleteIndex($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        self::$connection->indices()->delete($params);
    }

    /**
     * update mapping
     *
     * @return void
     */
    public function putMapping($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        self::$connection->indices()->putMapping($params);
    }

    /**
     * update settings
     *
     * @return void
     */
    public function putSettings($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        self::$connection->indices()->putSettings($params);
    }

    /**
     * close index
     *
     * @return void
     */
    public function close($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        self::$connection->indices()->close($params);
    }

    /**
     * open index
     *
     * @return void
     */
    public function open($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        self::$connection->indices()->open($params);
    }
    



    /**
     * refresh index
     *
     * @return void
     */
    public function refreshIndex($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        self::$connection->indices()->refresh($params);
    }

    /**
     * index document
     *
     * @return void
     */
    public function indexDocument($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        return self::$connection->index($params);
    }

    /**
     * index documents in bulk
     *
     * @return void
     */
    public function indexBulkDocuments($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        return self::$connection->bulk($params);
    }

    /**
     * get documents
     *
     * @return void
     */
    public function getDocument($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        
        try {
            return self::$connection->get($params);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * update documents
     *
     * @return void
     */
    public function updateDocument($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        return self::$connection->update($params);
    }

    /**
     * delete documents
     *
     * @return void
     */
    public function deleteDocument($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        return self::$connection->delete($params);
    }

    /**
     * search documents
     *
     * @return void
     */
    public function search($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        try {
            return self::$connection->search($params);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * count query result
     *
     * @return array
     */
    public function count($params)
    {
        if (!self::$connection) {
            $this->connect();
        }
        try {
            return self::$connection->count($params);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
