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

class Data
{

    /**
     * @var \Webkul\ElasticSearch\Model\SearchAdapter\Adapter
     */
    protected $_elasticAdapter;

    /**
     * @var \Webkul\ElasticSearch\Helper\Data
     */
    protected $_helper;

    /**
     * @var Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var string
     */
    protected $_type;

    /**
     * initialize variables
     */
    public function __construct(
        \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter $elasticAdapter,
        \Webkul\ElasticSearch\Helper\Data $helper
    ) {
        $this->_helper = $helper;
        $this->_elasticAdapter = $elasticAdapter;
    }

    /**
     * update data on elastic server
     *
     * @param $model
     * @param string $type
     * @return void
     */
    public function update($model, string $type)
    {
        if ($type) {
            $this->_type = $type;
            $this->_documentModel = $model;

            switch ($type) {
                case "product":
                    $index = $this->_helper->getIndexByType($this->_type);
                    if ($index->getMode() == \Webkul\ElasticSearch\Api\Data\IndexerInterface::UPDATE_ON_SAVE) {
                        $this->updateProductData();
                    } else {
                        $index->setStatus(\Webkul\ElasticSearch\Api\Data\IndexerInterface::REINDEX_REQUIRED);
                        $index->save();
                    }
                    break;

                case "pages":
                    $index = $this->_helper->getIndexByType($this->_type);
                    if ($index->getMode() == \Webkul\ElasticSearch\Api\Data\IndexerInterface::UPDATE_ON_SAVE) {
                        $this->updatePageData();
                    } else {
                        $index->setStatus(\Webkul\ElasticSearch\Api\Data\IndexerInterface::REINDEX_REQUIRED);
                        $index->save();
                    }
                    break;

                case "category":
                    $index = $this->_helper->getIndexByType($this->_type);
                    if ($index->getMode() == \Webkul\ElasticSearch\Api\Data\IndexerInterface::UPDATE_ON_SAVE) {
                        $this->updateCategoryData();
                    } else {
                        $index->setStatus(\Webkul\ElasticSearch\Api\Data\IndexerInterface::REINDEX_REQUIRED);
                        $index->save();
                    }
                    break;

                default:
                    throw new \Exception(__("invalid index type"));
            }
        }
    }

    /**
     * update product data on elastic
     *
     * @return boolean
     */
    public function updateProductData()
    {
        try {
            $document = $this->getUpdateSkeleton();
            $docData = $this->getProductUpdateData($this->_attributes);
            
            $index = $this->_helper->getIndexByType($this->_type);
            $checkDoc = [
                'index' => $this->_helper->getIndexName($index->getIndex()),
                'id' => $this->_documentModel->getId(),
            ];
            
            if ($this->_elasticAdapter->getDocument($checkDoc)) {
                $document['body']['doc'] = $docData;
                $this->_elasticAdapter->updateDocument($document);
            } else {
                $document['body'] = $docData;
                $this->_elasticAdapter->indexDocument($document);
            }
            return true;
        } catch (\Exception $e) {
            $this->_helper->createLog($e->getMessage());
            return false;
        }
    }

    /**
     * update page data on elastic
     *
     * @return boolean
     */
    public function updatePageData()
    {
        try {
            $document = $this->getUpdateSkeleton();
            $docData = $this->getPageUpdateData($this->_attributes);
            $index = $this->_helper->getIndexByType($this->_type);
            $checkDoc = [
                'index' => $this->_helper->getIndexName($index->getIndex()),
                'id' => $this->_documentModel->getId(),
            ];
            if ($this->_elasticAdapter->getDocument($checkDoc)) {
                $document['body']['doc'] = $docData;
                $this->_elasticAdapter->updateDocument($document);
            } else {
                $document['body'] = $docData;
                $this->_elasticAdapter->indexDocument($document);
            }
           
            return true;
        } catch (\Exception $e) {
            $this->_helper->createLog($e->getMessage());
            return false;
        }
    }

    /**
     * update category data on elastic
     *
     * @return boolean
     */
    public function updateCategoryData()
    {
        try {
            $document = $this->getUpdateSkeleton();
            $docData = $this->getCategoryUpdateData($this->_attributes);
           
            $index = $this->_helper->getIndexByType($this->_type);
            $checkDoc = [
                'index' => $this->_helper->getIndexName($index->getIndex()),
                'id' => $this->_documentModel->getId(),
            ];
            if ($this->_elasticAdapter->getDocument($checkDoc)) {
                $document['body']['doc'] = $docData;
                $this->_elasticAdapter->updateDocument($document);
            } else {
                $document['body'] = $docData;
                $this->_elasticAdapter->indexDocument($document);
            }
            return true;
        } catch (\Exception $e) {
            $this->_helper->createExceptionLog($e);
            return false;
        }
    }

    /**
     * get update array skeleton
     *
     * @return array
     */
    protected function getUpdateSkeleton()
    {
        $storeId = $this->_helper->getCurrentStoreId();
        $websiteId = $this->_helper->getCurrentWebsiteId();
        $index = $this->_helper->getIndexByType($this->_type);
        $params = [];
        if ($index) {
            $this->_attributes = $index->getAttributes();
            $params = [
                'index' => $this->_helper->getIndexName($index->getIndex()),
                'id' => $this->_documentModel->getId(),
                'body' => [
                    'doc' => []
                ]
            ];
            return $params;
        }

        return $params;
    }

    /**
     * get product attributes
     *
     * @param array $attributes
     * @return array
     */
    public function getProductUpdateData($attributes)
    {
        $document = [];
        $product = $this->_documentModel;
        foreach ($attributes as $attribute) {
            if ($attribute['type'] == 'boolean') {
                $document[$attribute['code']] = $product->getData($attribute['code']) == 1;
            } elseif ($attribute['type'] == 'array') {
                $value = $product->getData($attribute['code']);
                if (is_array($value) || $attribute['code'] == 'status') {
                    $document[$attribute['code']] = $value;
                } else {
                    $document[$attribute['code']] = explode(",", $value);
                }
            } elseif ($attribute['type'] == 'text') {
                $document[$attribute['code']] = $product->getData($attribute['code']);
                $document[$attribute['code']."_keyword"] = $product->getData($attribute['code']);
                $document[$attribute['code']."_suggest"] = [
                    'input' => $product->getData($attribute['code'])?substr($product->getData($attribute['code']), 0, 50):'null'
                ];

            } else {
                $document[$attribute['code']] = $product->getData($attribute['code']);
            }
        }
        $document['categories'] = $product->getCategoryIds();
        $date = new \DateTime($product->getCreatedAt());                    
        $document['created_at'] = $date->format('Y-m-d');
        return $document;
    }

    /**
     * get page attributes
     *
     * @param array $attributes
     * @return array
     */
    public function getPageUpdateData($attributes)
    {
        $page = $this->_documentModel;
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
                    'input' => $textValue?substr($textValue, 0, 50):'null'
                ];

            } else {
                $document[$attribute['code']] = $page->getData($attribute['code']);
            }
        }
        return $document;
    }

    /**
     * get category attributes
     *
     * @param array $attributes
     * @return array
     */
    public function getCategoryUpdateData($attributes)
    {
        $category = $this->_documentModel;
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
                $document[$attribute['code']."_suggest"] = [
                    'input' => $textValue
                ];
            }
        }
        return $document;
    }
}
