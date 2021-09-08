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
namespace Webkul\ElasticSearch\Model\SearchAdapter;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Search\AdapterInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Search\Response\QueryResponse;
use Webkul\ElasticSearch\Model\Adapter\ElasticAdapter;

class Adapter implements AdapterInterface
{
    protected $responseFactory;
 
    protected $connectionManager;
 
    protected $elastciAdapter;

    protected $_queryBuilder;

    protected $_currentDoc = [];
 
    public function __construct(
        ResponseFactory $responseFactory,
        ElasticAdapter $elastciAdapter,
        ResourceConnection $connectionManager,
        \Webkul\ElasticSearch\Model\SearchAdapter\Query\Builder $queryBuilder
    ) {
        $this->responseFactory = $responseFactory;
        $this->elastciAdapter = $elastciAdapter;
        $this->connectionManager = $connectionManager;
        $this->_queryBuilder = $queryBuilder;
    }
 
    /**
     * @param RequestInterface $request
     * @return QueryResponse
     */
    public function query(RequestInterface $request)
    {
        try {
            $countQuery = $this->_queryBuilder->getCountParams();

            $count = $this->elastciAdapter->count($countQuery);

            if (isset($count['count']) && $count['count'] > 0) {
                $query = $this->_queryBuilder->build($count['count']);         
                if ($query && count($query) > 0) {
	
                    $this->_currentDoc = $this->getDocument($query);
                }
	             
            } else {
                $this->_currentDoc['documents'] = [];
                $this->_currentDoc['aggregations'] = $this->_queryBuilder->getBlankBuckets();
                $this->_currentDoc['total'] = 0;
            }
        } catch (\Exception $e) {

            $this->_currentDoc['documents'] = [];
            $this->_currentDoc['aggregations'] = $this->_queryBuilder->getBlankBuckets();
            $this->_currentDoc['total'] = 0;
        }
        
        return $this->responseFactory->create($this->_currentDoc);
    }

    /**
     * get documents from elastic
     *
     * @return void
     */
    private function getDocument($query)
    {
        $documents = $this->elastciAdapter->search($query);
        $aggregations = $this->getAggregations($documents);
      
        if ($documents['hits']['total'] > 0) {
            $result = [];
            $aggregations = $this->getAggregations($documents);
            $result['aggregations'] = $aggregations;
            $result['total'] = isset($documents['hits']['total']) ? $documents['hits']['total']['value'] : 0;
            foreach ($documents['hits']['hits'] as $data) {
                $result['documents'][$data['_id']] = ['_id' => $data['_id'], 'score' => $data['_score']];
            }
            return $result;
        }

        return [
            'documents' => [],
            'aggregations' => $aggregations,
            'total' => 0
        ];
    }

    /**
     * get documents from elastic
     *
     * @return void
     */
    private function getAggregations($aggs)
    {
       
        $aggregations = [];
        if (isset($aggs['aggregations']) && count($aggs['aggregations']) > 0) {
            foreach ($aggs['aggregations'] as $key => $agg) {
         
                $aggregations[$key] = [];
                $bucketArray = [];
                if ($key == 'price_bucket') {
                    if (isset($agg['buckets'])) {
                        $bucketCount = count($agg['buckets']);
                        foreach ($agg['buckets'] as $bkey => $bucket) {
                            $bucketKey = '';
                            $bucketKey = sprintf(
                                "%d_%d",
                                $bucket['key'],
                                $bucket['key']+$this->_queryBuilder->getPriceInterval()
                            );
                            $bucketArray[$bucketKey] = [
                                'value' => $bucketKey,
                                'count' => $bucket['doc_count']
                            ];
                        }
                    }
                } else {
                    
                    if (isset($agg['buckets']) && count($agg['buckets']) > 0) {
                 
                        foreach ($agg['buckets'] as $bucket) {

                            $bucketArray[$bucket['key']] = [
                                'value' => $bucket['key'],
                                'count' => $bucket['doc_count']
                            ];
                        }
                    }
                }
                $aggregations[$key] = $bucketArray;
    
            }

            return $aggregations;
        }

        return $this->_queryBuilder->getBlankBuckets();
    }
    
    /**
     * get connection object for elastic search
     *
     * @return ResourceConnection
     */
    public function getConnection()
    {
        return $this->connectionManager->getConnection();
    }
}
