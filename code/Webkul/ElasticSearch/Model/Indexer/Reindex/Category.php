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
 * ElasticSearch product reindex.
 */
class Category
{
    
    const INDEX = 'catalog-category';
    const TYPE = 'category';
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
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    // protected $_productRepository;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $_categoryRepository;

    /**
     * construct
     */
    public function __construct(
        \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter $elasticAdapter,
        \Webkul\ElasticSearch\Helper\Data $helper,
        SearchCriteriaInterface $searchCreteria,
        \Magento\Catalog\Api\CategoryListInterface $categoryRepository
    ) {
        $this->_elasticAdapter = $elasticAdapter;
        $this->_helper = $helper;
        $this->searchCreteria = $searchCreteria;
        $this->_categoryRepository = $categoryRepository;
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
            $categoryCollection = $this->_categoryRepository->getList($this->searchCreteria);
    
            $documents = [];
            foreach ($categoryCollection->getItems() as $key => $category) {
                $doesExist = $this->_elasticAdapter->getDocument(['index' => $this->_helper->getIndexName(self::INDEX), 'type' => self::TYPE, 'id' => $category->getId()]);
                if ($doesExist) {
                    $documents['body'][] = [
                        'update' => [
                            '_index' => $this->_helper->getIndexName(self::INDEX),
                            '_id' => $category->getId()
                        ]
                    ];
                    $document = [];
                    foreach ($attributes as $attribute) {
                        
                        if ($attribute['code'] == 'products') {
                            $productIds = $category->getProductCollection()->getAllIds()?$category->getProductCollection()->getAllIds():[];
                            $document[$attribute['code']] = array_values($productIds);
                        } elseif ($attribute['code'] == 'path') {
                            if (is_array($category->getData($attribute['code']))) {
                                $document[$attribute['code']] = $category->getData($attribute['code']);
                            } else {
                                $path = explode("/", $category->getData($attribute['code']));
                                $document[$attribute['code']] = $path;
                            }
                        } else {
                            $textValue = $category->getData($attribute['code'])?$category->getData($attribute['code']):"null";
                            $textValue = strip_tags($textValue);
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
                        }
                    }
                    $documents['body'][]['doc'] = $document;

                } else {
                    $documents['body'][] = [
                        'index' => [
                            '_index' => $this->_helper->getIndexName(self::INDEX),
                            '_id' => $category->getId()
                        ]
                    ];
                    $document = [];
                    foreach ($attributes as $attribute) {
                    
                        if ($attribute['code'] == 'products') {
                            $productIds = $category->getProductCollection()->getAllIds()?$category->getProductCollection()->getAllIds():[];
                            $document[$attribute['code']] = array_values($productIds);
                        } elseif ($attribute['code'] == 'path') {
                            if (is_array($category->getData($attribute['code']))) {
                                $document[$attribute['code']] = $category->getData($attribute['code']);
                            } else {
                                $path = explode("/", $category->getData($attribute['code']));
                                $document[$attribute['code']] = $path;
                            }
                        } else {
                            $textValue = $category->getData($attribute['code'])?$category->getData($attribute['code']):"null";
                            $textValue = strip_tags($textValue);
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
