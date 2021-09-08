<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SampleProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SampleProduct\Helper;

use Webkul\SampleProduct\Model\SampleProductFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Webkul\SampleProduct\Model\SampleProductFactory
     */
    protected $_sampleProductFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param SampleProductFactory $sampleProductFactory
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface|null $productRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        SampleProductFactory $sampleProductFactory,
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository = null
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->_sampleProductFactory = $sampleProductFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository ?:ObjectManager::getInstance()
            ->get(ProductRepositoryInterface::class);
        parent::__construct($context);
    }

    public function isSampleProductEnable()
    {
        return $this->scopeConfig->getValue(
            'sampleproduct/settings/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function allowToLoginCustomer()
    {
        return $this->scopeConfig->getValue(
            'sampleproduct/settings/allow_to_login_customer',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function allowCustomerGroups($storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->_storeManager->getStore()->getCode();
        }
        try {
            return $this->scopeConfig->getValue(
                'sampleproduct/settings/allow_customer_groups',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } catch (\Magento\Framework\Exception\State\InitException $e) {
            throw NoSuchEntityException::singleField('storeId', $storeId);
        } catch (NoSuchEntityException $e) {
            throw NoSuchEntityException::singleField('storeId', $storeId);
        }
    }

    /**
     * @inheritdoc
     */
    public function getCustomGroupById($groupId)
    {
        try {
            return $this->groupRepository->getById($groupId);
        } catch (NoSuchEntityException $e) {
            throw NoSuchEntityException::singleField('groupId', $groupId);
        }
    }

    public function maxSampleQty()
    {
        return $this->scopeConfig->getValue(
            'sampleproduct/settings/max_sample_qty',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isCurrentSampleProduct($productId)
    {
        $collection = $this->_sampleProductFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('sample_product_id', $productId);
        return $collection->getSize();
    }

    public function isCurrentSampleProductById($productId)
    {
        $collection = $this->_sampleProductFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter('product_id', $productId);
        if($collection->getSize() > 0){
            return true;
        }else{
            return false;
        }
    }

    public function getSampleProductIdByProductId($productId)
    {
        $sampleProductId = '';
        $collection = $this->_sampleProductFactory->create()
            ->getCollection()
            ->addFieldToFilter('product_id', $productId);
        foreach ($collection as $value) {
            $sampleProductId = $value->getSampleProductId();
        }
        return $sampleProductId;
    }

    public function getSampleParentProductId($sampleProductId)
    {
        $productId = '';
        $collection = $this->_sampleProductFactory->create()
            ->getCollection()
            ->addFieldToFilter('sample_product_id', $sampleProductId);
        foreach ($collection as $value) {
            $productId = $value->getProductId();
        }
        return $productId;
    }

    public function getSampleProductByProductId($productId)
    {
        $sampleProductId = $this->getSampleProductIdByProductId($productId);
        return $this->productFactory->create()->load($sampleProductId);
    }

    public function getSampleParentProduct($sampleProductId)
    {
        $productId = $this->getSampleParentProductId($sampleProductId);
        return $this->productFactory->create()->load($productId);
    }

    public function getAllSampleProducts()
    {
        return $collection = $this->_sampleProductFactory->create()
            ->getCollection();
    }
}
