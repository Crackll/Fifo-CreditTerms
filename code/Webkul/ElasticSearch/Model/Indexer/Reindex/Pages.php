<?php
/**
 * Webkul Software.
 *
 * @category Webkul_ElasticSearch
 *
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ElasticSearch\Model\Indexer\Reindex;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * ElasticSearch cms pages reindex.
 */
class Pages
{
    
    const INDEX = 'cms';
    const TYPE = 'pages';
    /**
     * @var \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter
     */
    protected $_elasticAdapter;

    /**
     * @var \Webkul\ElasticSearch\Helper\Data
     */
    protected $_helper;

    /**
     * @var SearchCriteriaInterface
     */
    protected $searchCreteria;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $_pageRepository;

    /**
     * construct
     */
    public function __construct(
        \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter $elasticAdapter,
        \Webkul\ElasticSearch\Helper\Data $helper,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCreteria,
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository
    ) {
        $this->_elasticAdapter = $elasticAdapter;
        $this->_helper = $helper;
        $this->searchCreteria = $searchCreteria;
        $this->_pageRepository = $pageRepository;
    }
    /**
     * do reindex
     *
     * @return void
     */
    public function doReindex($cli = false)
    {
        $collection = $this->_helper->getIndexrs();
        $collection->addFieldToFilter("type", ['eq' => self::TYPE]);
        $attributes = [];
        foreach ($collection as $index) {
            $attributes = $index->getAttributes();
        }

        if (is_array($attributes)) {
            $searchCreteria = $this->searchCreteria->addFilter(
                'store_id',
                [$this->_helper->getCurrentStoreId(),0],
                'in'
            )->create();
            $pageCollection = $this->_pageRepository->getList($searchCreteria);
           
            $documents = [];
            foreach ($pageCollection->getItems() as $key => $page) {
               
                $doesExist = $this->_elasticAdapter->getDocument(['index' => $this->_helper->getIndexName(self::INDEX), 'type' => self::TYPE, 'id' => $page->getId()]);
                if ($doesExist) {
                    $documents['body'][] = [
                        'update' => [
                            '_index' => $this->_helper->getIndexName(self::INDEX),
                            '_id' => $page->getId()
                        ]
                    ];
                    $document = [];
                    foreach ($attributes as $attribute) {
                        if ($attribute['type'] == 'array') {
                            $value = $page->getData($attribute['code']);
                            if (is_array($value)) {
                                $document[$attribute['code']] = $value;
                            } else {
                                $document[$attribute['code']] = explode(",", $value);
                            }
                        
                        } elseif ($attribute['type'] == 'text') {
                            $textValue = strip_tags($page->getData($attribute['code']));
                            $document[$attribute['code']] = $textValue;
                            $document[$attribute['code']."_keyword"] = $textValue;
                            $document[$attribute['code']."_autocomplete"] = $textValue;
                            $document[$attribute['code']."_suggest"] = [
                                'input' => $textValue
                            ];

                        } else {
                            $document[$attribute['code']] = $page->getData($attribute['code']);
                        }

                    }
                    $documents['body'][]['doc'] = $document;
                    
                } else {
                    $documents['body'][] = [
                        'index' => [
                            '_index' => $this->_helper->getIndexName(self::INDEX),
                            '_id' => $page->getId()
                        ]
                    ];
                    $document = [];
                    foreach ($attributes as $attribute) {
                        if ($attribute['type'] == 'array') {
                            $value = $page->getData($attribute['code']);
                            if (is_array($value)) {
                                $document[$attribute['code']] = $value;
                            } else {
                                $document[$attribute['code']] = explode(",", $value);
                            }
                        
                        } elseif ($attribute['type'] == 'text') {
                            $textValue = strip_tags($page->getData($attribute['code']));
                            $document[$attribute['code']] = $textValue;
                            $document[$attribute['code']."_keyword"] = $textValue;
                            $document[$attribute['code']."_autocomplete"] = $textValue;
                            if ($textValue) {
                                $splitString = explode(" ", $textValue);
                                $words = implode(" ", array_splice($splitString, 0, 25));
                                $document[$attribute['code']."_suggest"] = [
                                    'input' =>$words
                                ];
                            } else {
                                $document[$attribute['code']."_suggest"] = [
                                    'input' => 'null'
                                ];
                            }

                        } else {
                            $document[$attribute['code']] = $page->getData($attribute['code']);
                        }

                    }
                    $documents['body'][] = $document;
                }
            }

            $responses = $this->_elasticAdapter->indexBulkDocuments($documents);
            if (isset($responses["errors"]) && $responses["errors"]) {
                $this->_helper->createLog($responses);
                throw new \Exception(__("Something went wrong, please check logs"));
            }
            unset($responses);
        }
        return true;
    }
}
