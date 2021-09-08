<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCategory
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCategory\Model;

use Webkul\MpSellerCategory\Api\Data\CategoryInterface;
use Magento\Framework\DataObject\IdentityInterface as Identity;
use Magento\Framework\Model\AbstractModel;
use Webkul\MpSellerCategory\Model\ResourceModel\Product\CollectionFactory as SellerCategoryProductCollection;

class Category extends AbstractModel implements CategoryInterface, Identity
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * MpSellerCategory Category cache tag.
     */
    const CACHE_TAG = 'mpsellercategory_category';

    /**
     * @var string
     */
    protected $_cacheTag = 'mpsellercategory_category';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'mpsellercategory_category';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param SellerCategoryProductCollection $sellerCategoryProductCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\ResponseInterface $request,
        SellerCategoryProductCollection $sellerCategoryProductCollectionFactory
    ) {
        $this->_request = $request;
        $this->_sellerCategoryProductCollectionFactory = $sellerCategoryProductCollectionFactory;
        parent::__construct($context, $registry);
    }

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpSellerCategory\Model\ResourceModel\Category::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteCategory();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Category.
     *
     * @return \Webkul\MpSellerCategory\Model\Category
     */
    public function noRouteCategory()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpSellerCategory\Api\Data\CategoryInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Processing object after load data
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $productIds = [];
        try {
            $collection = $this->_sellerCategoryProductCollectionFactory->create();
            $collection->addFieldToFilter("seller_category_id", $this->getId());
            foreach ($collection as $item) {
                $productIds[] = $item->getProductId();
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $productIds = implode(",", $productIds);
        $this->setProductIds($productIds);
        return parent::_afterLoad();
    }

    /**
     * Processing object after save data
     *
     * @return $this
     */
    public function afterSave()
    {
        try {
            $productIds = $this->getData("product_ids");
            $collection = $this->_sellerCategoryProductCollectionFactory->create();
            $collection->addFieldToFilter("seller_category_id", $this->getId());
            $collection->removeCategories();
            if (!empty($productIds)) {
                if (strpos($productIds, ",") !== false) {
                    $productIds = explode(",", $productIds);
                } else {
                    $productIds = [$productIds];
                }

                $productIds = array_unique($productIds);
                $collection->assignProducts($this->getId(), $productIds);
            }
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
        }

        return parent::afterSave();
    }
}
