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

namespace Webkul\ElasticSearch\Model\SearchAdapter\Query;

class Builder
{
    /**
     * @var \Webkul\ElasticSearch\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $_categoryRepository;

    public function __construct(
        \Webkul\ElasticSearch\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        $this->_helper = $helper;
        $this->registry = $registry;
        $this->request = $request;
        $this->_categoryRepository = $categoryRepository;
    }

    /**
     * build query
     *
     * @return array
     */
    public function build($size)
    {

        $params = [];
        $query = $this->getQueryString();
        if (!$query) {
            $params = [
                'index' => $this->_helper->getIndexName('catalog'),
                'body' => [
                    'from' => 0,
                    'size' => $size,
                    'query' => $this->getQuery(),
                    'aggs' =>  $this->getAggrigationData()
                ]
            ];
        } else {
            $params = [
                'index' => $this->_helper->getIndexName('catalog'),
                'body' => [
                    'from' => 0,
                    'size' => $size,
                    'query' => $this->getQuery(),
                    'aggs' =>  $this->getAggrigationData()
                ]
            ];
        }
        return $params;
    }

    /**
     * get query for multi search
     *
     * @return void
     */
    private function getQuery()
    {
        if ($this->isAdvancedSearch()) {
            return $this->getAdvancedQuery();
        }
        $queryType = $this->_helper->getSearchType();
        switch ($queryType) {
            case 'multi_match':
                return $this->getMultiMatchQuery();
                break;
            case 'match':
                return $this->getSimpleMatchQuery();
                break;
            default:
                return $this->getMultiMatchQuery();
        }
    }

    /**
     * get multi match query for store front search
     *
     * @return void
     */
    private function getMultiMatchQuery()
    {
        if ($this->getQueryString()) {
            $multiMatch = [];
            if (in_array(
                $this->_helper->getSearchMultiMatchType(),
                ['cross_fields', 'phrase', 'phrase_prefix']
            )) {
                $multiMatch = [
                    "query" =>    $this->getQueryString(),
                    "fields" => $this->_helper->getSearchFields(),
                    "type" =>   $this->_helper->getSearchMultiMatchType(),
                    'operator' => $this->_helper->getSearchOperator()
                ];
            } else {
                $multiMatch = [
                    "query" =>    $this->getQueryString(),
                    "fuzziness" => $this->_helper->getFuzziness(),
                    "fields" => $this->_helper->getSearchFields(),
                    "type" =>   $this->_helper->getSearchMultiMatchType(),
                    'operator' => $this->_helper->getSearchOperator()
                ];
            }
            $query = [
                'bool' => [
                    'should' => [
                        'multi_match' => $multiMatch
                    ],
                    'filter' => $this->getFilters()
                ]
            ];
            if ($this->_helper->getMinimumShouldMatch()) {
                $query['bool']['should']['multi_match']['minimum_should_match'] =
                $this->_helper->getMinimumShouldMatch();
            }
            return $query;
        } else {
            return [
                'bool' => [
                    'filter' => $this->getFilters()
                ]
            ];
        }
    }

    //query for advanced search
    private function getAdvancedQuery()
    {
        $searchableAttributes = $this->_helper->getSearchableAttrbutes();
        
        $matches = [];
        foreach ($searchableAttributes as $sa) {
            if (!$sa['is_filterable']) {
                if ($this->request->getParam($sa['code'])) {
                   
                    $query = [
                        'match' => [
                            $sa['code'] => $this->request->getParam($sa['code'])
                        ]
                    ];
                    
                    array_push($matches, $query);
                }
            }
        }
        
        return [
            'bool' => [
                'must' => $matches,
                'filter' => $this->getFilters()
            ]
        ];
    }

    /**
     * get multi match query for store front search
     *
     * @return void
     */
    private function getSimpleMatchQuery()
    {
        if ($this->getQueryString()) {
            return [
                'bool' => [
                    'must' => [
                        'match' => [
                            'name' => [
                                "query" =>    $this->getQueryString(),
                                "fuzziness" => $this->_helper->getFuzziness()
                            ]
                        ]
                    ],
                    'filter' => $this->getFilters()
                ]
            ];
        } else {
            return [
                'bool' => [
                    'filter' => $this->getFilters()
                ]
            ];
        }
    }

    //query for advanced search
    private function get()
    {
        $searchableAttributes = $this->_helper->getSearchableAttrbutes();
        
        $matches = [];
        foreach ($searchableAttributes as $sa) {
            if (!$sa['is_filterable']) {
                if ($this->request->getParam($sa['code'])) {
                   
                    $query = [
                        'match' => [
                            $sa['code'] => $this->request->getParam($sa['code'])
                        ]
                    ];
                    
                    array_push($matches, $query);
                }
            }
        }
        
        return [
            'bool' => [
                'must' => $matches,
                'filter' => $this->getFilters()
            ]
        ];
    }

    /**
     * create filter request data
     *
     * @return array
     */
    private function getFilters()
    {
        $filterableAttributes = $this->_helper->getFilterableAttrbutes();
        $filters = [];
        if ($this->getCategoryId()) {
            if (is_array($this->getCategoryId())) {
                array_push($filters, ['terms' => ['categories' => $this->getCategoryId()]]);
            } else {
                array_push($filters, ['term' => ['categories' => $this->getCategoryId()]]);
            }
        }
        //only get enabled products
        array_push($filters, ['term' => ['status' => 1]]);
        foreach ($filterableAttributes as $attribute) {
            $filterValue = $this->request->getParam($attribute['code']);
            if (isset($filterValue) && $filterValue) {
                $filterType = 'term';
                if ($attribute['type'] == 'boolean') {
                    $filterType = 'term';
                    $filterValue = $filterValue==1;
                } elseif ($attribute['type'] == 'double_range') {
                    $filterType = 'range';
                    $range = [];
                    if (is_array($filterValue)) {
                        if (isset($filterValue['from']) && $filterValue['from']) {
                            $range[0] = $filterValue['from'];
                        } else {
                            $range[0] = 0;
                        }
                        if (isset($filterValue['to']) && $filterValue['to']) {
                            $range[1] = $filterValue['to'];
                        } else {
                            $range[1] = null;
                        }
                    } else {
                        $range = explode("-", $filterValue);
                    }
                    
                    $filterValue = [];
                    if ($range[0] == null) {
                        $filterValue = ['lte' => $range[1]];
                    } elseif ($range[1] == null) {
                        $filterValue = ['gte' => $range[0]];
                    } else {
                        $filterValue = ['gte' => $range[0], 'lte' => $range[1]];
                    }
                }
                array_push($filters, [$filterType => [$attribute['code'] => $filterValue]]);
            }
        }
        
        return array_values($filters);
    }

    /**
     * get current query string
     *
     * @return string
     */
    public function getQueryString()
    {
        return $this->request->getParam("q");
    }

    /**
     * get current page
     *
     * @return string
     */
    public function getPageNumber()
    {
        return $this->request->getParam("p")?$this->request->getParam("p"):0;
    }

    /**
     * get price interval
     *
     * @return string
     */
    public function getPriceInterval()
    {
        return 10;
    }

    /**
     * get price interval
     *
     * @return string
     */
    public function getPageSize()
    {
        return 9;
    }

    /**
     * get filter category id
     *
     * @return int
     */
    private function getCategoryId()
    {
        $categoryId = 0;
        if ($this->request->getParam("cat")) {
            $categoryId = $this->request->getParam("cat");
        }
        if ($this->registry->registry("current_category")) {
            $categoryId = $this->registry->registry("current_category")->getId();
        }

        if ($categoryId) {
            $category = $this->_categoryRepository->get($categoryId);
            // echo $category->getChildren()."////".$category->getDisplayMode();die;
            if ($category->getDisplayMode() == 'PRODUCTS' || $category->getDisplayMode() == null) {
                if ($category->getChildren()) {
                    $categoryIds = explode(",", $category->getChildren());
                    array_push($categoryIds, $categoryId);
                    return $categoryIds;
                } else {
                    return $categoryId;
                }
            } else {
                return $categoryId;
            }
        }

        return 0;
    }

    /**
     * get aggrigation data
     *
     * @return array
     */
    public function getAggrigationData()
    {
        $filterableAttributes = $this->_helper->getFilterableAttrbutes();
        $aggrgations = [];
        foreach ($filterableAttributes as $attr) {
            $bucket = sprintf("%s_bucket", $attr['code']);
            
            $bucketData = [];
            if ($attr['type'] == 'double_range') {
                $aggrgations[$bucket] = [];
                    $bucketData = [ 'histogram' => [
                        'field' => $attr['code'],
                        'interval' => $this->getPriceInterval(),
                        "min_doc_count" => 0
                    ]
                    ];
                    $aggrgations[$bucket] = $bucketData;
            } else {
                $aggrgations[$bucket] = [];
                $bucketData = [ 'terms' => [
                        'field' => $attr['code'],
                        "min_doc_count" => 0
                    ]
                ];
                $aggrgations[$bucket] = $bucketData;
            }
        }
        $aggrgations['category_bucket'] = ['terms' => [
            'field' => 'categories',
            "min_doc_count" => 0
        ]];

        return $aggrgations;
    }

    /**
     * get blank buckets
     *
     * @return array
     */
    public function getBlankBuckets()
    {
        $filterableAttributes = $this->_helper->getFilterableAttrbutes();
        $aggrgations = [];
        foreach ($filterableAttributes as $attr) {
            $aggrgations[$attr['code']."_bucket"] = [];
        }
        $aggrgations['category_bucket'] = [];
        return $aggrgations;
    }

    public function isAdvancedSearch()
    {
        
        $controller = $this->request->getControllerName();

        if ($controller == "advanced") {
            return true;
        }
        return false;
    }

    /**
     * get documents count
     *
     * @return array
     */
    public function getCountParams()
    {
        if ($this->isAdvancedSearch()) {
            return [
                'index' => $this->_helper->getIndexName('catalog'),
                'body' => [
                    'query' => $this->getAdvancedQuery()
                ]
            ];
        }
        $params = [];
    
        $query = $this->getQueryString();

        if (!$query) {
            $params = [
                'index' => $this->_helper->getIndexName('catalog'),
                'body' => [
                    'query' => $this->getQuery()
                ]
            ];
        } else {
            $params = [
                'index' => $this->_helper->getIndexName('catalog'),
                'body' => [
                    'query' => $this->getQuery()
                ]
            ];
        }
        return $params;
    }
}
