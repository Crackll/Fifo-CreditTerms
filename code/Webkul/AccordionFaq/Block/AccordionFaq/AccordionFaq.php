<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AccordionFaq
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AccordionFaq\Block\AccordionFaq;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class AccordionFaq extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Webkul\AccordionFaq\Model\ResourceModel\Addfaq\CollectionFactory
     */
    protected $_faqCollection;

    /**
     * @var \Webkul\AccordionFaq\Model\ResourceModel\Gallery\CollectionFactory
     */
    protected $_faqgroupCollection;

    /**
     * @var \Webkul\AccordionFaq\Model\ResourceModel\Groups\CollectionFactory
     */
    protected $_groupCollection;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    protected $_moduleManager;

    protected $jsonhelper;
    protected $scopeconfig;

    /**
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\AccordionFaq\Model\ResourceModel\Addfaq\CollectionFactory $faq
     * @param \Webkul\AccordionFaq\Model\ResourceModel\Faqgroup\CollectionFactory $faqgroup
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Webkul\AccordionFaq\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\AccordionFaq\Model\ResourceModel\Addfaq\CollectionFactory $faq,
        \Webkul\AccordionFaq\Model\ResourceModel\Faqgroup\CollectionFactory $faqgroup,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Json\Helper\Data $jsonhelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeconfig,
        array $data = []
    ) {
    
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_faqCollection = $faq;
        $this->_faqgroupCollection = $faqgroup;
        $this->_storeManager = $context->getStoreManager();
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_filesystem = $context->getFilesystem();
        $this->_filterProvider = $filterProvider;
        $this->_moduleManager=$moduleManager;
        $this->jsonhelper = $jsonhelper;
        $this->scopeconfig = $scopeconfig;
        parent::__construct($context, $data);
    }

    /**
     * Prepare layout.
     *
     * @return this
     */
    public function _prepareLayout()
    {
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle('FAQ');
        }

        return parent::_prepareLayout();
    }

    /**
     * Get Images Collection.
     *
     * @return collection object
     */
    public function getFaqCollection()
    {
        $collection = $this->_faqCollection->create();
        $collection->addFieldToFilter('status', 1);

        return $collection;
    }
    /**
     * Get Groups Collection.
     *
     * @return collection object
     */
    public function getGroupCollection()
    {
        $collection = $this->_faqgroupCollection->create();
        $collection->addFieldToFilter('status', 1);

        return $collection;
    }

    /**
     * Get Faq Ids From Group Code.
     *
     * @param int $groupCode
     *
     * @return array
     */
    public function getFaqIdsFromGroupCode($groupCode)
    {
        $groupCollection = $this->getGroupCollection();
        $groupCollection->addFieldToFilter('group_code', $groupCode);
        $faqIds = '';
        foreach ($groupCollection as $group) {
            $faqIds = $group->getFaqIds();
        }
        if ($faqIds != '') {
            if (strpos($faqIds, ',') !== false) {
                return explode(',', $faqIds);
            } else {
                return [$faqIds];
            }
        } else {
            return [];
        }
    }

    /**
     * Get FAQ Collection by Selected faq Ids.
     *
     * @param int|array $faqIds
     *
     * @return Collection|null
     */
    public function getSelectedFaqCollection($faqIds)
    {
        $faqCollection = $this->getFaqCollection();
        $faqCollection->addFieldToFilter('id', ['in' => $faqIds])
                      ->setOrder('sort_order', 'ASC');
        return $faqCollection;
    }
    /**
     * Get width from group code.
     *
     * @param string|array $groupCode
     *
     * @return $width|null
     */
    public function getWidth($groupCode)
    {
        $width=null;
        $groupCollection = $this->getGroupCollection();
        $groupCollection->addFieldToFilter('group_code', $groupCode);
        foreach ($groupCollection as $group) {
            $width = $group->getWidth();
            break;
        }
        return $width;
    }
    /**
     * Prepare HTML content
     *
     * @return string
     */
    public function getCmsFilterContent($value = '')
    {
        $html = $this->_filterProvider->getPageFilter()->filter($value);
        return $html;
    }

    public function getModuleEnabled()
    {
        return $this->_moduleManager->isEnabled('Webkul_AccordionFaq');
    }

    public function getJsonHelper()
    {
        return $this->jsonhelper;
    }

    public function getEnableValue()
    {
        return $this->scopeconfig->getValue('AccordionFaq/AccordionFaq_settings/enable_faq');
    }
}
