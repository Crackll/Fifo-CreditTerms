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

class CmsPageSaveAfter implements ObserverInterface
{


    protected $_indexUpdate;
    protected $_helper;

     /**
      * store emulater
      *
      * @var Emulation
      */
    protected $_emulate;

    public function __construct(
        \Webkul\ElasticSearch\Model\Indexer\Update\Data $indexUpdate,
        \Webkul\ElasticSearch\Helper\Data $helper,
        Emulation $emulate
    ) {
        $this->_indexUpdate = $indexUpdate;
        $this->_helper = $helper;
        $this->_emulate = $emulate;
    }

    public function execute(Observer $observer)
    {
        if ($this->_helper->canUseElastic()) {
            $page = $observer->getObject();
            $storeIds = [];

            if (in_array(0, $page->getStoreId())) {
                $stores = $this->_helper->getAllStores();
                foreach ($stores as $store) {
                    $storeIds[] = $store->getId();
                }
            } else {
                $storeIds = $page->getStoreId();
            }
            foreach ($storeIds as $storeId) {
                /**
                 * $initialEnvironmentInfo store emulation start
                 */
                $initialEnvironmentInfo = $this->_emulate->startEnvironmentEmulation($storeId);
                $this->_indexUpdate->update($page, 'pages');
                $this->_emulate->stopEnvironmentEmulation($initialEnvironmentInfo);
            }
        }
    }
}
