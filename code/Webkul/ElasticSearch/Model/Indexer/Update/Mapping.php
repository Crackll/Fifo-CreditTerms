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
namespace Webkul\ElasticSearch\Model\Indexer\Update;

class Mapping
{

    /**
     * @var \Webkul\ElasticSearch\Helper\Data
     */
    protected $_helper;

    /**
     * @var Webkul\ElasticSearch\Model\Adapter\ElasticAdapter
     */
    protected $_elasticAdapter;

    public function __construct(
        \Webkul\ElasticSearch\Helper\Data $helper,
        \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter $elasticAdapter
    ) {
        $this->_helper = $helper;
        $this->_elasticAdapter = $elasticAdapter;
    }

    /**
     * put elastic mapping
     *
     * @param object $index
     * @return void
     */
    public function updateMapping($index)
    {
        $properties = $this->_helper->getMappingProperties($index);
        $mapping = [
            'index' => $this->_helper->getIndexName($index->getIndex()),
            'body' => [
                'properties' => $properties
            ]
        ];
        try {
            $this->_elasticAdapter->putMapping($mapping);
        } catch (\Exception $e) {
            $this->_helper->createExceptionLog($e);
            throw new \Exception(__("mapping can not be updated, please goto command line and delete the old index at store id %1 and reindex again to update mapping", $this->_helper->getCurrentStoreId()));
        }
        return true;
    }

    /**
     * put elastic settings
     *
     * @param object $index
     * @return void
     */
    public function updateSettings($index)
    {
        $filters = $this->_helper->getFilterSettings();
        $analyzers = $this->_helper->getAnalyzerSettings();
        
        $analysis = [];
        $charFilters = $this->_helper->getCharFilterSettings();
        if (count($charFilters) > 0) {
            $analysis = [
                
                "filter" => $filters,
                "char_filter" => $charFilters,
                "analyzer" => $analyzers
                    
            ];
        } else {
            $analysis = [
                
                "filter" => $filters,
                "analyzer" => $analyzers
                    
            ];
        }
        $settings = [
            'index' => $this->_helper->getIndexName($index->getIndex()),
            'body' => [
                'analysis' => $analysis
            ]
        ];
        $this->_elasticAdapter->putSettings($settings);
        return true;
    }
}
