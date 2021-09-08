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
class Product
{

    const INDEX = 'catalog';
    const TYPE = 'product';
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
    protected $_productRepository;

    protected $_categoryFactory;

    /**
     * construct
     */
    public function __construct(
        \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter $elasticAdapter,
        \Webkul\ElasticSearch\Helper\Data $helper,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCreteria,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Json\Helper\Data $jsonData,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        $this->_jsonData = $jsonData;
        $this->_elasticAdapter = $elasticAdapter;
        $this->_helper = $helper;
        $this->searchCreteria = $searchCreteria;
        $this->_productRepository = $productRepository;
        $this->_categoryFactory = $categoryFactory;
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
                'visibility',
                1,
                'nin'
            )->addFilter('store_id', $this->_helper->getCurrentStoreId() ,'eq')
            ->create();


            
            $productCollection = $this->_productRepository->getList($searchCreteria);
            //check to stop execution if there are too many products
            if ($productCollection->getTotalCount() > 1000 && !$cli) {
                throw new \Exception(__("please use console command to reindex, since product count is too much to handle"));
            }
            if ($productCollection->getTotalCount() == 0) {
                throw new \Exception(__("no products available to index"));
            }
            $params = [];
            $documents = [];
            $i = 0;
            foreach ($productCollection->getItems() as $key => $product) {
                $doesExist = $this->_elasticAdapter->getDocument(['index' => $this->_helper->getIndexName(self::INDEX), 'type' => self::TYPE, 'id' => $product->getId()]);
                if ($doesExist) {
                    $params['body'][] = [
                        'update' => [
                            '_index' => $this->_helper->getIndexName(self::INDEX),
                            '_id' => $product->getId()
                        ]
                    ];
                    $document = [];
                    foreach ($attributes as $attribute) {
                        if ($attribute['type'] == 'boolean') {
                            $document[$attribute['code']] = $product->getData($attribute['code']) == 1;
                        } elseif ($attribute['type'] == 'array') {
                            $value = $product->getData($attribute['code']);
                            if ($product->gettypeId()=="configurable") {
                                $value = $this->getAttributeValueForConfig($product, $attribute);
                            }
                            if (is_array($value)) {
                                $document[$attribute['code']] = $value;
                            } else {
                                $document[$attribute['code']] = explode(",", $value);
                            }
                        } elseif ($attribute['type'] == 'text') {
                            $textValue = strip_tags($product->getData($attribute['code']));
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
                            if ($product->gettypeId()=="configurable" && $attribute['type']=='double_range') {
                                $document[$attribute['code']]= $this->getAttributeValueForConfig($product, $attribute);
                            } else {
                                $document[$attribute['code']] = $product->getData($attribute['code']);
                            }
                        }
                    }
                    $document['categories'] = $product->getCategoryIds();
                    $date = new \DateTime($product->getCreatedAt());                    
                    $document['created_at'] = $date->format('Y-m-d');
                    foreach ($product->getCategoryIds() as $categoryId) {
                        $category = $this->_categoryFactory->create()->load($categoryId);
                        $positions = $category->getProductsPosition();
                        $document['position_'.$categoryId] = $positions[$product->getId()];
                    }
                    $params['body'][]['doc'] = $document;
                } else {
                    $params['body'][] = [
                        'index' => [
                            '_index' => $this->_helper->getIndexName(self::INDEX),
                            '_id' => $product->getId()
                        ]
                    ];
                    $document = [];
                    foreach ($attributes as $attribute) {
                        if ($attribute['type'] == 'boolean') {
                            $document[$attribute['code']] = $product->getData($attribute['code']) == 1;
                        } elseif ($attribute['type'] == 'array') {
                            $value = $product->getData($attribute['code']);
                            if ($product->gettypeId()=="configurable") {
                                $value = $this->getAttributeValueForConfig($product, $attribute);
                            }
                            if (is_array($value) || $attribute['code'] == 'status') {
                                $document[$attribute['code']] = $value;
                            } else {
                                $document[$attribute['code']] = explode(",", $value);
                            }
                        } elseif ($attribute['type'] == 'text') {
                            $textValue = strip_tags($product->getData($attribute['code']));
                            $document[$attribute['code']] = $textValue;
                            $document[$attribute['code']."_autocomplete"] = $textValue;
                            $document[$attribute['code']."_keyword"] = $textValue;
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
                            if ($product->gettypeId()=="configurable" && $attribute['type']=='double_range') {
                                $document[$attribute['code']] = $this->getAttributeValueForConfig($product, $attribute);
                            } else {
                                if ($attribute['code'] == 'status') {
                                    $status = $product->getData($attribute['code']);
                                    if (is_array($status)) {
                                        $document[$attribute['code']] = $status[0];
                                    } else {
                                        $document[$attribute['code']] = $status;
                                    }
                                } else {
                                    $document[$attribute['code']] = $product->getData($attribute['code']);
                                }
                                $document[$attribute['code']] = $product->getData($attribute['code']);
                            }
                        }
                    }
                    $document['categories'] = $product->getCategoryIds();
                    $date = new \DateTime($product->getCreatedAt());                    
                    $document['created_at'] = $date->format('Y-m-d');
                    foreach ($product->getCategoryIds() as $categoryId) {
                        $category = $this->_categoryFactory->create()->load($categoryId);
                        $positions = $category->getProductsPosition();
                        $document['position_'.$categoryId] = $positions[$product->getId()];
                    }
                    $params['body'][] = $document;
                }
                $i++;
                if ($i % 1000 == 0) {
                    $responses = $this->_elasticAdapter->indexBulkDocuments($params);
                    $params = ['body' => []];
                    if (isset($responses["errors"]) && $responses["errors"]) {
                        $this->_helper->createLog($responses);
                        throw new \Exception(__("Something went wrong, please check logs"));
                    }
                    unset($responses);
                }
            }
            if ($params['body'] && count($params['body']) > 0) {
                $responses = $this->_elasticAdapter->indexBulkDocuments($params);
                if (isset($responses["errors"]) && $responses["errors"]) {
                    $this->_helper->createLog($responses);
                    throw new \Exception(__("Something went wrong, please check logs"));
                }
            }
        }
        return true;
    }

    public function getAttributeValueForConfig($product, $attribute)
    {
        $attrValue = "";
        $price = 0;
        $childProductIds = $product->getTypeInstance()->getUsedProductIds($product);
        if ($product->getData($attribute['code'])!="") {
            $attrValue = $product->getData($attribute['code']);
        }
        if ($attribute['code']=='price') {
            $price =  $product->getData($attribute['code']);
        }
        foreach ($childProductIds as $childId) {
            $childProduct = $this->_productRepository->getById($childId);
            if ($childProduct->getData($attribute['code'])!="") {
                $attrValue = $attrValue.','.$childProduct->getData($attribute['code']);
            }
            if ($attribute['code']=='price') {
                if ($price==0 || $childProduct->getData($attribute['code'])>0 && $childProduct->getData($attribute['code'])<$price) {
                    $price =  $childProduct->getData($attribute['code']);
                }
            }
        }
        if ($attribute['type'] == 'array') {
            return $attrValue;
        } elseif ($attribute['type'] == 'double_range') {
            return $price;
        }
    }
}
