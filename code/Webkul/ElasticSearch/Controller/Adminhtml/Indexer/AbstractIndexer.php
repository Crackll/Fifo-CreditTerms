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
namespace Webkul\ElasticSearch\Controller\Adminhtml\Indexer;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Store\Model\App\Emulation;

/**
 * Webkul ElasticSearch Abstract
 */
abstract class AbstractIndexer extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $_resultPage;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * File Uploader factory.
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Webkul\ElasticSearch\Helper\Data
     */
    protected $_elasticHelper;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_timezoneInterface;
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var Webkul\ElasticSearch\Model\Adapter\ElasticAdapter
     */
    protected $_elasticAdapter;

    /**
     * @var \Webkul\ElasticSearch\Model\IndexerFactory
     */
    protected $_indexerFactory;

    /**
     * @var indexPool[]
     */
    protected $indexPool = [];

    /**
     * store emulater
     *
     * @var Emulation
     */
    protected $_emulate;

   /**
    * @param Context $context
    * @param PageFactory $resultPageFactory
    * @param ForwardFactory $resultForwardFactory
    * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    * @param Filesystem $filesystem
    * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
    * @param \Magento\Store\Model\StoreManagerInterface $storeManager
    * @param \Magento\Framework\Registry $coreRegistry
    * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
    * @param \Webkul\ElasticSearch\Helper\Data $elasticHelper
    * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    * @param Filter $filter
    * @param \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter $elasticAdapter
    * @param \Webkul\ElasticSearch\Model\IndexerFactory $indexerFactory
    * @param Emulation $emulate
    * @param array $indexPool
    */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\ElasticSearch\Helper\Data $elasticHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        Filter $filter,
        \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter $elasticAdapter,
        \Webkul\ElasticSearch\Model\IndexerFactory $indexerFactory,
        Emulation $emulate,
        array $indexPool = []
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_date = $date;
        $this->_elasticHelper = $elasticHelper;
        $this->_timezoneInterface = $timezoneInterface;
        $this->_filter = $filter;
        $this->_elasticAdapter = $elasticAdapter;
        $this->_indexerFactory = $indexerFactory;
        $this->_emulate = $emulate;
        $this->indexPool = $indexPool;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_ElasticSearch::indexer');
    }

    /**
     * get json response object
     *
     * @return Magento\Framework\Controller\Result
     */
    public function getJsonResponse(array $content)
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($content);

        return $resultJson;
    }
}
