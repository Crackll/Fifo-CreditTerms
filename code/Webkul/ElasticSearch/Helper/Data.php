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
namespace Webkul\ElasticSearch\Helper;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Api\SearchCriteriaInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * ElasticSearch helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ELASTIC_CONFIG = 'elasticsearch/settings/';
    const ELASTIC_SEARCH_CONFIG = "elasticsearch/search_settings/";
    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * Customer session.
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Directory\Model\Config\Source\Country
     */
    protected $_country;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var SearchCriteriaInterface
     */
    protected $searchCreteria;

    /**
     * @var Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMeta;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $cacheList;

    protected $_logger;

    /**
     * @var \Webkul\ElasticSearch\Model\IndexerFactory
     */
    protected $_indexerFactory;

    /**
     * @var [type]
     */
    protected $_synonymCollection;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $_eavAttributesRepo;

    protected $collectionFactory;

    /**
     * @param Magento\Framework\App\Helper\Context        $context
     * @param Magento\Directory\Model\Currency            $currency
     * @param Magento\Customer\Model\Session              $customerSession
     * @param Magento\Framework\UrlInterface              $url
     * @param Magento\Catalog\Model\ResourceModel\Product $product
     * @param Magento\Store\Model\StoreManagerInterface   $_storeManager
     */
    public function __construct(
        Session $customerSession,
        \Magento\Framework\App\Helper\Context $context,
        FormKeyValidator $formKeyValidator,
        DateTime $date,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Directory\Model\Config\Source\Country $country,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Filesystem $fileSystem,
        SearchCriteriaInterface $searchCreteria,
        \Magento\Framework\App\ProductMetadataInterface $productMeta,
        \Magento\Config\Model\ResourceModel\Config $configWriter,
        \Magento\Framework\App\Cache\TypeListInterface $cacheList,
        LoggerInterface $logger,
        \Webkul\ElasticSearch\Model\IndexerFactory $indexer,
        \Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory $synonymCollection,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributesRepo,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
    ) {
        
        $this->_date = $date;
        $this->_customerSession = $customerSession;
        $this->_objectManager = $objectManager;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        $this->_country  = $country;
        $this->_regionFactory = $regionFactory;
        $this->fileSystem = $fileSystem;
        $this->searchCreteria = $searchCreteria;
        $this->productMeta = $productMeta;
        $this->configWriter = $configWriter;
        $this->cacheList = $cacheList;
        $this->_logger = $logger;
        $this->_indexerFactory = $indexer;
        $this->_synonymCollection = $synonymCollection;
        $this->_eavAttributesRepo = $eavAttributesRepo;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * create log
     *
     * @return void
     */
    public function createLog($data)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/elastic-debug.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $logger->info($data);
    }

    /**
     * create log
     *
     * @return void
     */
    public function createCronLog($data)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/elastic-cron-debug.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $logger->info($data);
    }

    /**
     * create exception log
     *
     * @return void
     */
    public function createExceptionLog($data)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/elastic-exception.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $logger->info($data);
    }

    /**
     * get search engine
     *
     * @return string
     */
    public function getEngine()
    {
        return $this->scopeConfig->getValue(
            "catalog/search/engine",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * is elastic enable
     *
     * @return bool
     */
    public function canUseElastic()
    {
        if ($this->getEngine() == 'elastic') {
            return true;
        }

        return false;
    }

    /**
     * get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->scopeConfig->getValue(
            self::ELASTIC_CONFIG.'host',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get port
     *
     * @return string
     */
    public function getPort()
    {
        return $this->scopeConfig->getValue(
            self::ELASTIC_CONFIG.'port',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get elastic index prefix
     *
     * @return string
     */
    public function getIndexPrefix()
    {
        return $this->scopeConfig->getValue(
            self::ELASTIC_CONFIG.'index_prefix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get search type
     *
     * @return string
     */
    public function getSearchType()
    {
        return $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get search fields for multi search
     *
     * @return string
     */
    public function getSearchFields()
    {
        $fields = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'search_fields',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $fields = explode(",", $fields);

        if ($fields && is_array($fields)) {
            if (in_array("name", $fields)) {
                array_push($fields, "name_autocomplete^2");
            }
            if (in_array("sku", $fields)) {
                array_push($fields, "sku_autocomplete");
            }
            return $fields;

        } elseif ($fields) {
            return  [$fields];
        } else {
            ['name', 'name_autocomplete^2', 'sku', 'sku_autocomplete'];
        }
    }

    /**
     * get search fields for multi search
     *
     * @return string
     */
    public function getPriceLimit()
    {
        $limit = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'price_to',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $fields = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'search_fields',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $fields = explode(",", $fields);
        $limit = $limit?$limit:0;
        if (in_array("price", $fields)) {
            return $limit;
        }
        return 0;
    }

    /**
     * get multi match search type
     *
     * @return string
     */
    public function getSearchMultiMatchType()
    {
        return $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'multi_match_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get search operator
     *
     * @return string
     */
    public function getSearchOperator()
    {
        return $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'operator',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get minimum should match
     *
     * @return string
     */
    public function getMinimumShouldMatch()
    {
        $percent = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'minimum_match',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($percent) {
            return $percent."%";
        }
        
        return '';
    }

    /**
     * get wildcard expression
     *
     * @return string
     */
    public function getWildcard()
    {
        return $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'wildcard',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get regex expression
     *
     * @return string
     */
    public function getRegex()
    {
        return $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'regex',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get highlight search
     *
     * @return string
     */
    public function getHighlightSearch()
    {
        return $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'highlight',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get search autocorrect
     *
     * @return string
     */
    public function getFuzziness()
    {
        return $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'fuzziness',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get search filters
     *
     * @return string
     */
    public function getSearchFilters()
    {
        $filters = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'filters',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($filters && !is_array($filters)) {
            return explode(",", $filters);
        } elseif (is_array($filters)) {
            return $filters;
        }

        return false;
    }

    /**
     * get elision articles
     *
     * @return string
     */
    public function getElisionArticles()
    {
        $articles = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'elision_articles',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getCurrentStoreId()
        );
        if ($articles) {
            return explode(",", $articles);
        }

        return '';
    }

    /**
     * get use stop word filter
     *
     * @return string
     */
    public function getIsStopWord()
    {
        $status = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'use_stop_filter',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getCurrentStoreId()
        );

        return $status;
    }

    /**
     * get stop words
     *
     * @return string
     */
    public function getStopWords()
    {
        $words = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'stop_words',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getCurrentStoreId()
        );
        
        if ($words) {
            return explode(", ", $words);
        }

        return false;
    }

    /**
     * get language
     *
     * @return string
     */
    public function getLanguageStemmer()
    {
        $lang = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'stemmer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getCurrentStoreId()
        );
        
        if ($lang) {
            return $lang;
        }

        return 'english';
    }

    /**
     * get stem marker
     *
     * @return string
     */
    public function getStemmerMarker()
    {
        $marker = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'remove_from_stemming',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getCurrentStoreId()
        );
        
        if ($marker) {
            return explode(", ", $marker);
        }

        return '';
    }

    /**
     * get can use char filter
     *
     * @return string
     */
    public function canUseCharFilters()
    {
        $status = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'use_char_filter',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return 1;
    }

    /**
     * get char filter
     *
     * @return string
     */
    public function getCharFilters()
    {
        $filters = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'char_filters',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($filters && !is_array($filters)) {
            $filters = explode(",", $filters);
        }

        if (!$this->getMappingFilter()) {
            $filters =  array_diff($filters, ['mapping']);
        }
        if (!$this->getFilterPattern()) {
            $filters = array_diff($filters, ['pattern_replace']);
        }

        return $filters;
    }

    /**
     * get can use char filter
     *
     * @return string
     */
    public function getMappingFilter()
    {
        $mapping = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'mapping_filter',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getCurrentStoreId()
        );
        if ($mapping) {
            $articlesArray = explode(",", $mapping);
            return $articlesArray;
        }

        return '';
    }

    /**
     * get filter pattern
     *
     * @return string
     */
    public function getFilterPattern()
    {
        $pattern = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'pattern_replace_filter',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getCurrentStoreId()
        );

        return $pattern;
    }

    /**
     * get replace filter pattern
     *
     * @return string
     */
    public function getReplaceFilterPattern()
    {
        $replacement = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'replacement',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getCurrentStoreId()
        );
        
        return $replacement?$replacement:" ";
    }

    /**
     * remove from stemming
     *
     * @return void
     */
    public function getRemoveFromStemming()
    {
        $stemming = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'remove_from_stemming',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        if ($stemming) {
            return explode(", ", $stemming);
        }

        return '';
    }

    /**
     * get hot search autocomplete
     *
     * @return string
     */
    public function getHotSearch()
    {
        $hotSearchTerm = $this->scopeConfig->getValue(
            self::ELASTIC_SEARCH_CONFIG.'hot_search',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return explode(",", $hotSearchTerm);
    }

    /**
     * reset connection setting
     * @return string
     */
    public function resetConfig()
    {

        $this->configWriter->saveConfig(
            self::ELASTIC_CONFIG.'host',
            "",
            'default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->configWriter->saveConfig(
            self::ELASTIC_CONFIG.'port',
            "",
            'default',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $this->cacheList->cleanType("config");
    }

    /**
     * get indexers
     *
     * @return void
     */
    public function getIndexrs()
    {

        return $this->_indexerFactory->create()->getCollection();
    }

    /**
     * get index by type
     *
     * @return void
     */
    public function getIndexByType($type)
    {
        
        $collection = $this->_indexerFactory->create()->getCollection()->addFieldToFilter('type', ['eq' => $type]);
        foreach ($collection as $model) {
            return $model;
        }

        return false;
    }

    /**
     * get filterable product attributes
     *
     * @return void
     */
    public function getFilterableAttrbutes()
    {
        $indexes = $this->getIndexrs()->addFieldToFilter("type", ['eq' => 'product']);
        $filterableAttributes = [];
        foreach ($indexes as $index) {
            $attributes = $index->getAttributes();
            foreach ($attributes as $attrbute) {
                if (isset($attrbute['is_filterable']) && $attrbute['is_filterable']) {
                    $filterableAttributes[] = $attrbute;
                }
            }
        }
        return $filterableAttributes;
    }

    /**
     * get filterable product attributes
     *
     * @return void
     */
    public function getSearchableAttrbutes()
    {
        $indexes = $this->getIndexrs()->addFieldToFilter("type", ['eq' => 'product']);
        $searchableAttributes = [];
        foreach ($indexes as $index) {
            $attributes = $index->getAttributes();
            foreach ($attributes as $attrbute) {
                if ($attrbute['type'] == 'text' && isset($attrbute['is_searchable']) && $attrbute['is_searchable'] && (isset($attrbute['is_filterable']) && !$attrbute['is_filterable'])) {
                    $searchableAttributes[] = $attrbute;
                }
            }
        }
        return $searchableAttributes;
    }

    /**
     * crate indexer data
     *
     * @param \Webkul\ElasticSearch\Api\Data\IndexerInterface $index
     * @return void
     */
    public function getIndexCreateData(\Webkul\ElasticSearch\Api\Data\IndexerInterface $index)
    {
        
        if ($index->getId()) {
            $analysis = [];
            $charFilters = $this->getCharFilterSettings();
            if ($charFilters && count($charFilters) > 0) {
                $analysis = [
                    
                    "filter" => $this->getFilterSettings(),
                    "char_filter" => $charFilters,
                    "analyzer" => $this->getAnalyzerSettings()
                        
                ];
            } else {
                $analysis = [
                    
                    "filter" => $this->getFilterSettings(),
                    "analyzer" => $this->getAnalyzerSettings()
                        
                ];
            }
            $indexData = [
                'index' => $this->getIndexName($index->getIndex()),
                'body' => [
                    'settings' => [
                        'number_of_shards' => 5,
                        'number_of_replicas' => 0,
                        'analysis' => $analysis
                    ],
                    'mappings' => [
                        
                        '_source' => [
                            'enabled' => true
                        ],
                        'properties' => $this->getMappingProperties($index)
                        
                    ]
                ]
            ];

            return $indexData;
        }
    }

    /**
     * get mapping properties
     *
     * @return array
     */
    public function getMappingProperties($index)
    {
        $mappings = [];
        $attributes = $index->getAttributes();
        $attributeMapping = [];
        foreach ($attributes as $attribute) {
            if ($attribute['type'] == 'array') {
                $attributeMapping[$attribute['code']] = [
                    "type" => "text",
                    "fielddata" => true,
                    'boost' => $attribute['boost']
                ];
            } elseif ($attribute['type'] == 'boolean') {
                $attributeMapping[$attribute['code']] = [
                    'type' => $attribute['type'],
                    'boost' => $attribute['boost']
                ];
            } elseif ($attribute['type'] == 'double_range') {
                $attributeMapping[$attribute['code']] = [
                    'type' => 'double',
                    'boost' => $attribute['boost']
                ];
            } else {
                $attributeMapping[$attribute['code']] = [
                    'type' => $attribute['type'],
                    'boost' => $attribute['boost'],
                    "analyzer" => $this->getLanguageStemmer()
                ];
                $attributeMapping[$attribute['code']."_autocomplete"] = [
                    "type" => "text",
                    "analyzer" => "autocomplete",
                    "search_analyzer" => "standard",
                    "fielddata" => true
                ];
                $attributeMapping[$attribute['code']."_suggest"] = [
                    'type' => 'completion',
                    "analyzer" => $this->getLanguageStemmer()
                ];
                $attributeMapping[$attribute['code']."_keyword"] = [
                    'type' => 'keyword',
                    'boost' => $attribute['boost']
                ];
            }
        }
        //extra fields other then product attributes
        if ($index->getType() == "product") {
            $attributeMapping['categories'] = [
                "type" => "text",
                "fielddata" => true,
                'boost' => 1
            ];

            $attributeMapping['created_at'] = [
                "type" => "date",
                'boost' => 1
            ];

            $allCatIds = $this->collectionFactory->create()->getAllIds();
            foreach ($allCatIds as $id) {
                $attributeMapping['position_'.$id] = [
                    "type" => "integer",
                    'boost' => 1
                ];
            }
        }

        return $attributeMapping;
    }

    /**
     * get filters for mapping
     *
     * @return array
     */
    public function getFilterSettings()
    {
        $filters = ['elision', 'lowercase', 'synonym', 'stop', 'keyword_marker', 'stemmer'];
        $searchFilters = $this->getSearchFilters();
        $buildFilters = [];
        foreach ($filters as $filter) {
            switch ($filter) {
                case 'elision':
                    if ($searchFilters && in_array('elision', $searchFilters)) {
                        if ($this->getElisionArticles()) {
                            $buildFilters[$filter] = [
                                'type' => $filter,
                                'articles' => $this->getElisionArticles()
                            ];
                        }
                    }
                    break;
                case 'lowercase':
                    $buildFilters[$filter] = [
                        'type' => $filter,
                        'articles' => $this->getElisionArticles()
                    ];
                    break;
                case 'synonym':
                    if ($searchFilters && in_array($filter, $searchFilters)) {
                        $synonyms = $this->getSynonyms();
                        if (is_array($synonyms) && count($synonyms) === 0) {
                            throw new \Exception(__("No synonyms found to create sysnonym filter"));
                        }
    
                        $buildFilters[$filter] = [
                            'type' => $filter,
                            'synonyms' => $this->getSynonyms()
                        ];
                    }
                    break;
                case 'stop':
                    if ($this->getIsStopWord()) {
                        
                        $buildFilters[$filter] = [
                            'type' => $filter,
                            'stopwords' => $this->getStopWords()?$this->getStopWords():'_'.$this->getLanguageStemmer().'_'
                        ];
                        
                    }
                    break;
                case 'keyword_marker':
                    if ($this->getRemoveFromStemming()) {
                        $buildFilters[$filter] = [
                            'type' => $filter,
                            'keywords' => $this->getRemoveFromStemming()?$this->getRemoveFromStemming():[]
                        ];
                    }
                    break;
                case 'stemmer':
                    $buildFilters[$filter] = [
                        'type' => $filter,
                        'language' => $this->getLanguageStemmer()
                    ];
            }
        }
        $buildFilters['autocomplete_filter'] = [
            "type" => "nGram",
            "min_gram" => "3",
            "max_gram" => "4"
        ];

        return $buildFilters;
    }

    /**
     * get char filters for mapping
     *
     * @return array
     */
    public function getCharFilterSettings()
    {
        $filters = ['html_strip', 'mapping', 'pattern_replace'];
        $searchFilters = $this->getCharFilters();
        $buildFilters = [];
        foreach ($filters as $filter) {
            switch ($filter) {
                case 'mapping':
                    if ($this->getMappingFilter() && $searchFilters && in_array($filter, $searchFilters)) {
                        $buildFilters[$filter] = [
                            'type' => $filter,
                            'mappings' => $this->getMappingFilter()
                        ];
                    }
                    break;
                case 'html_strip':
                    if ($searchFilters && in_array($filter, $searchFilters)) {
                        $buildFilters[$filter] = [
                            'type' => $filter
                        ];
                    }
                    break;
                case 'pattern_replace':
                    if ($searchFilters && in_array($filter, $searchFilters)) {
                        if ($this->getFilterPattern()) {
                            $buildFilters[$filter] = [
                                'type' => $filter,
                                "pattern" => $this->getFilterPattern(),
                                "replacement"=> $this->getReplaceFilterPattern()
                            ];
                        }
                    }
            }
        }

        return $buildFilters;
    }

    /**
     * get analyzers for mapping
     *
     * @return array
     */
    public function getAnalyzerSettings()
    {
        $searchFilters = $this->getSearchFilters();
        if (!$searchFilters || !is_array($searchFilters)) {
            $searchFilters = [];
        }
        array_push($searchFilters, 'stemmer');
        $charFilters =  $this->getCharFilters();
        $defaultAnalyzer = [];
        if ($this->getCharFilterSettings() && count($this->getCharFilterSettings()) > 0) {
            $defaultAnalyzer = [
                "tokenizer" => "standard",
                "filter" => $searchFilters,
                "char_filter" => $charFilters
            ];
        } else {
            $defaultAnalyzer = [
                "tokenizer" => "standard",
                "filter" => $searchFilters
            ];
        }
        return [
            $this->getLanguageStemmer() => $defaultAnalyzer,
            "autocomplete" => [
                "type" => "custom",
                "tokenizer" => "standard",
                "filter" => [
                    "lowercase",
                    "autocomplete_filter"
                ]
            ]
        ];
    }

    /**
     * get all syninyms defined in magento
     *
     * @return array
     */
    public function getSynonyms()
    {
        $collection = $this->_synonymCollection->create();
        $synonymGroup = [];
        if ($collection->getSize() > 0) {
            foreach ($collection as $model) {
                $synonymGroup[] = $model->getSynonyms();
            }
        }

        return $synonymGroup;
    }

        /**
         * get suffixed index name
         *
         * @return void
         */
    public function getIndexName($index, $storeId = 0)
    {
        $indexPrefix = $this->getIndexPrefix();
        if ($storeId) {
            $store = $this->_storeManager->getStore($storeId);
            return sprintf("%s-%s-%d-%d", $indexPrefix, $index, $store->getWebsiteId(), $store->getId());
        }
        return sprintf("%s-%s-%d-%d", $indexPrefix, $index, $this->getCurrentWebsiteId(), $this->getCurrentStoreId());
    }

    /**
     * current store Id
     *
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * current website Id
     *
     * @return int
     */
    public function getCurrentWebsiteId()
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    /**
     * current store code
     *
     * @return string
     */
    public function getCurrentStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    /**
     * get all stores
     *
     * @return string
     */
    public function getAllStores()
    {
        return $this->_storeManager->getStores();
    }

    /**
     * update product attributes mapping
     *
     * @return bool
     */
    public function updateAttributesMapping()
    {
        $index = $this->getIndexByType("product");
        $attributeInfo = $this->_eavAttributesRepo->getList(ProductAttributeInterface::ENTITY_TYPE_CODE, $this->searchCreteria);
        $searchableAttrbutes = [];
        $fieldMapping = [
            'select' => 'array',
            'text' => 'text',
            'price' => 'double_range',
            'date' => 'date',
            'textarea' => 'text',
            'multiselect' => 'array',
            'boolean' => 'boolean',
            'multiline' => 'array'
        ];

        foreach ($attributeInfo->getItems() as $attributes) {
            $attributeId = $attributes->getAttributeId();
            if ($attributeId && ($attributes->getIsSearchable() || $attributes->getIsFilterable())) {
                $attributeType = isset($fieldMapping[$attributes->getFrontendInput()])?$fieldMapping[$attributes->getFrontendInput()]:'text';
                $searchableAttrbutes[$attributeId] = [
                    'code' => $attributes->getAttributeCode(),
                    'type' => $attributeType,
                    'boost' => $attributes->getSearchWeight(),
                    'is_filterable' => $attributes->getIsFilterable(),
                    'is_searchable' => $attributes->getIsSearchable(),
                    'attribute' => 'default'
                ];

            }
        }
        $index->setStatus(\Webkul\ElasticSearch\Api\Data\IndexerInterface::REINDEX_REQUIRED);
        $index->setAttributes($searchableAttrbutes);
        $index->save();
    }

    /**
     * get price
     *
     * @return void
     */
    public function getPriceFilterStep()
    {
    }
}
