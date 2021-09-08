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
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Store\Model\App\Emulation;

class CatalogCategorySaveAfter implements ObserverInterface
{

    /**
     * @var \Webkul\ElasticSearch\Model\Indexer\Update\Data
     */
    protected $_categoryIndexUpdate;

    /**
     * @var Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $_categoryRepository;

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

    public function __construct(
        \Webkul\ElasticSearch\Model\Indexer\Update\Data $categoryIndexUpdate,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Webkul\ElasticSearch\Helper\Data $helper,
        Emulation $emulate
    ) {
        $this->_categoryIndexUpdate = $categoryIndexUpdate;
        $this->_categoryRepository = $categoryRepository;
        $this->_helper = $helper;
        $this->_emulate = $emulate;
    }

    public function execute(Observer $observer)
    {
        if ($this->_helper->canUseElastic()) {
            $storeIds = [];
            $category = $observer->getCategory();
            if ($category->getStoreId() != 0) {
                $storeIds[1] = $category->getStoreId();
            } else {
                $stores = $this->_helper->getAllStores();
                foreach ($stores as $store) {
                    $storeIds[] = $store->getId();
                }
            }
            foreach ($storeIds as $storeId) {
        
                /**
                 * $initialEnvironmentInfo store emulation start
                 */
                $initialEnvironmentInfo = $this->_emulate->startEnvironmentEmulation($storeId);
                try {
                    $this->_categoryIndexUpdate->update($this->_categoryRepository->get($category->getId(), $storeId), 'category');
                } catch (\Exception $e) {
                    $this->_helper->createExceptionLog($e);
                }
                $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
            }
        }
    }
}
