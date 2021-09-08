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
namespace Webkul\MpSellerCategory\Ui\DataProvider;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;
use Webkul\MpSellerCategory\Model\ResourceModel\Category\CollectionFactory;
use Webkul\Marketplace\Helper\Data as HelperData;

class CategoryListDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Webkul\MpSellerCategory\Model\ResourceModel\Category\Collection
     */
    protected $collection;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Webkul\MpSellerCategory\Helper\Data $helper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->helper = $helper;
        $this->collection->addFieldToFilter(
            'seller_id',
            ['eq' => $this->helper->getSellerId()]
        );
    }
}
