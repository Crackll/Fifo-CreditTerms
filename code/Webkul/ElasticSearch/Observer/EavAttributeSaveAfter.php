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

class EavAttributeSaveAfter implements ObserverInterface
{

    /**
     * @var \Webkul\ElasticSearch\Model\Indexer\Update\Data
     */
    protected $_productIndexUpdate;

    /**
     * @var Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

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
        \Webkul\ElasticSearch\Model\Indexer\Update\Data $productIndexUpdate,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Webkul\ElasticSearch\Helper\Data $helper,
        Emulation $emulate
    ) {
        $this->_productIndexUpdate = $productIndexUpdate;
        $this->_productRepository = $productRepository;
        $this->_helper = $helper;
        $this->_emulate = $emulate;
    }

    public function execute(Observer $observer)
    {
        if (!$this->_helper->canUseElastic()) {
            return $this;
        }
        $storeIds = [];
        $attribute = $observer->getEvent()->getAttribute();
    }
}
