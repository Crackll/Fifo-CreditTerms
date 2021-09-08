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

class Delete implements ObserverInterface
{

    /**
     * @var \Webkul\ElasticSearch\Data\Helper
     */
    protected $_helper;

    /**
     * store emulater
     *
     * @var Emulation
     */
    protected $_emulate;

    /**
     * @var Webkul\ElasticSearch\Model\Adapter\ElasticAdapter
     */
    protected $_elasticAdapter;

    public function __construct(
        \Webkul\ElasticSearch\Helper\Data $helper,
        \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter $elasticAdapter,
        Emulation $emulate
    ) {
        $this->_helper = $helper;
        $this->_elasticAdapter = $elasticAdapter;
        $this->_emulate = $emulate;
    }

    public function execute(Observer $observer)
    {
        $model = $observer->getObject();
        
        if ($this->_helper->canUseElastic()) {
            $class = get_class($model);
        
            if (strpos($class, 'Magento\\Cms\\Model\\Page') !== false) {
                $this->deletePages($model);
            } elseif (strpos($class, 'Magento\\Catalog\\Model\\Product') !== false) {
                $this->deleteProduct($model);
            } elseif (strpos($class, 'Magento\\Catalog\\Model\\Category') !== false) {
                $this->deleteCategory($model);
            }
        }
    }

    /**
     * delete product from elastic index
     *
     * @param Magento\Catalog\Model\Product $product
     * @return void
     */
    private function deleteProduct($product)
    {
        $stores = $this->_helper->getAllStores();

        foreach ($stores as $store) {
            $storeId = $store->getId();
            /**
             * $initialEnvironmentInfo store emulation start
             */
            $initialEnvironmentInfo = $this->_emulate->startEnvironmentEmulation($storeId);
            $index = $this->_helper->getIndexByType('product');
            $params = [
                'index' => $this->_helper->getIndexName($index->getIndex()),
                'type' => $index->getType(),
                'id' => $product->getId()
            ];
            if ($this->_elasticAdapter->getDocument($params)) {
                try {
                    $this->_elasticAdapter->deleteDocument($params);
                } catch (\Exception $e) {
                    $this->_helper->createExceptionLog($e);
                }
            }
            
            $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
        }
    }

    /**
     * delete category from elastic index
     *
     * @param Magento\Catalog\Model\Category
     * @return void
     */
    private function deleteCategory($category)
    {
        $stores = $this->_helper->getAllStores();
        
        foreach ($stores as $store) {
            $storeId = $store->getId();
            /**
             * $initialEnvironmentInfo store emulation start
             */
            $initialEnvironmentInfo = $this->_emulate->startEnvironmentEmulation($storeId);
            $index = $this->_helper->getIndexByType('category');
            $params = [
                'index' => $this->_helper->getIndexName($index->getIndex()),
                'type' => $index->getType(),
                'id' => $category->getId()
            ];
            if ($this->_elasticAdapter->getDocument($params)) {
                try {
                    $this->_elasticAdapter->deleteDocument($params);
                } catch (\Exception $e) {
                    $this->_helper->createExceptionLog($e);
                }
            }
            $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
        }
    }

    /**
     * delete cms pages from elastic index
     *
     * @param Magento\Cms\Model\Page $page
     * @return void
     */
    private function deletePages($page)
    {
        $index = $this->_helper->getIndexByType('pages');
        $params = [
            'index' => $this->_helper->getIndexName($index->getIndex()),
            'type' => $index->getType(),
            'id' => $page->getId()
        ];
        if ($this->_elasticAdapter->getDocument($params)) {
            try {
                $this->_elasticAdapter->deleteDocument($params);
            } catch (\Exception $e) {
                $this->_helper->createExceptionLog($e);
            }
        }
    }
}
