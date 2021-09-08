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

use Magento\Framework\Exception\LocalizedException;

use Magento\Framework\Exception;

class Index extends AbstractIndexer
{
    /**
     * index list
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_WebToPrint::indexer');
        $resultPage->getConfig()->getTitle()->prepend(__('Elastic Indexes'));
        return $resultPage;
    }
}
