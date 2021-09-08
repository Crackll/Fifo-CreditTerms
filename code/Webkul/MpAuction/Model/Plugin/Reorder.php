<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpAuction
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Model\Plugin;

use \Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Registry;

class Reorder
{
    /**
     * @var Magento\Framework\App\State
     */
    protected $_appState;
    /**
     * @var \Magento\Sales\Controller\AbstractController\OrderLoaderInterface
     */
    protected $orderLoader;

    /**
     * @var Registry
     */
    protected $_coreRegistry;
    /**
     * @param \Magento\Framework\App\State     $appState
     */
    public function __construct(
        \Webkul\MpAuction\Model\ProductFactory $auctionProFactory,
        Action\Context $context,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect
    ) {
        $this->_auctionProFactory = $auctionProFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $subject
     * @param \Closure $proceed
     * @param [type] $attribute
     * @param boolean $joinType
     * @return void
     */
    public function aroundExecute(
        \Magento\Sales\Controller\AbstractController\Reorder $subject,
        \Closure $proceed
    ) {
        $redirectUrl = $this->redirect->getRedirectUrl();
        $orderId=$subject->getRequest()->getParam('order_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        $isAuctionReorder=$this->_auctionProFactory->create()->getCollection()
        ->addFieldToFilter('order_id', ['eq'=>$orderId])
        ->getSize();
        if (!$isAuctionReorder) {
            $result = $proceed();
            return $result;
        } else {
            $this->messageManager->addWarningMessage(
                __('You can\'t reorder the auction product .')
            );
            return $resultRedirect->setPath($redirectUrl);
        }
        return true;
    }
}
