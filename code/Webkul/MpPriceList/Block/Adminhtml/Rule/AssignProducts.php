<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPriceList\Block\Adminhtml\Rule;

use Webkul\MpPriceList\Model\ResourceModel\Items\CollectionFactory as ItemsCollection;

class AssignProducts extends \Magento\Backend\Block\Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'rule/products.phtml';

    /**
     * @var \Magento\Catalog\Block\Adminhtml\Category\Tab\Product
     */
    protected $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * AssignProducts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param ItemsCollection $itemsCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        ItemsCollection $itemsCollection,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_itemsCollection = $itemsCollection;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \Webkul\MpPriceList\Block\Adminhtml\Rule\Products::class,
                'mppricelist.rule.product.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * @return array|null
     */
    public function getRule()
    {
        return $this->_coreRegistry->registry('mppricelist_rule');
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = array_keys($this->getSelectedProducts());
        return $products;
    }

    /**
     * get selected product json
     *
     * @return array
     */
    public function getSelectedProductsJson()
    {
        $jsonProducts = [];
        $products = array_keys($this->getSelectedProducts());
        if (!empty($products)) {
            foreach ($products as $product) {
                $jsonProducts[$product] = true;
            }
            return $this->_jsonEncoder->encode($jsonProducts);
        }
        return '{}';
    }

    /**
     * get selected products
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        $allProducts = $this->getRequest()->getPost('pricelist_rule_products');
        $products = [];
        if ($allProducts === null) {
            $rule = $this->getRule();
            $id = $rule->getId();
            $collection = $this->_itemsCollection
                                ->create()
                                ->addFieldToFilter("entity_type", 1)
                                ->addFieldToFilter("parent_id", $id);
            foreach ($collection as $product) {
                $products[$product->getEntityValue()] = ['position' => $product->getEntityValue()];
            }
        } else {
            foreach ($allProducts as $product) {
                $products[$product] = ['position' => $product];
            }
        }
        return $products;
    }
}
