<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Controller\Account;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Webkul\MpAuction\Model\ProductFactory as AuctionProduct;

class Masscancel extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface
{
    const CANCEL_BY_OWNER = 3;
   
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    private $filter;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helperData;

    /**
     * @var Webkul\Auction\Model\ProductFactory
     */
    private $auctionProduct;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\MpAuction\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Model\Url $modelUrl
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $helperData,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpAuction\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\Url $modelUrl,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->filter = $filter;
        $this->helperData = $helperData;
        $this->messageManager = $messageManager;
        $this->collectionFactory = $collectionFactory;
        $this->_customerSession  = $customerSession;
        $this->modelUrl = $modelUrl;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        parent::__construct($context);
    }

    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->modelUrl->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    public function execute()
    {
        try {
            $isPartner = $this->helperData->isSeller();
            if ($isPartner == 1) {
                  $collection = $this->filter->getCollection($this->collectionFactory->create());
                  $recordCancel = 0;
                foreach ($collection->getItems() as $auctionProduct) {
                    if ($auctionProduct->getAuctionStatus() == 0 || $auctionProduct->getAuctionStatus() == 1) {
                          $auctionProduct->setId($auctionProduct->getEntityId());
                          $auctionProduct->setAuctionStatus(self::CANCEL_BY_OWNER);
                          $auctionProduct->setExpired(1);
                          $this->saveObj($auctionProduct);
                          $recordCancel++;
                    }
                }

                $this->messageManager->addSuccess(__('A total of %1 record(s) have been cancel.', $recordCancel));
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/auctionlist',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            } else {
                 return $this->resultRedirectFactory->create()->setPath(
                     'marketplace/account/becomeseller',
                     ['_secure' => $this->getRequest()->isSecure()]
                 );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/auctionlist',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
     /**
      * saveObj
      * @param Object
      * @return void
      */
    private function saveObj($object)
    {
        $object->save();
    }
}
