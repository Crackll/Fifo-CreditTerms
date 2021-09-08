<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Controller\Adminhtml\Leads;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Edit extends Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Controller\Result\Forward
     **/
    protected $resultForward;

    /**
     * @var \Webkul\MpWholesale\Helper\Data
     **/
    protected $helper;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\MpWholesale\Helper\Data $helper
     * @param \Magento\Framework\Controller\Result\Forward $resultForward
     */
    public function __construct(
        Context $context,
        \Webkul\MpWholesale\Helper\Data $helper,
        \Magento\Framework\Controller\Result\Forward $resultForward,
        ResultFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->resultForward = $resultForward;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $moduleEnabled = $this->helper->getModuleStatus();
        if (!$moduleEnabled) {
            $resultForward = $this->resultForward;
            $resultForward->forward('noroute');
            return $resultForward;
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Lead Details'));
        
        $block = \Webkul\MpWholesale\Block\Adminhtml\Lead\Details::class;
        $content = $resultPage->getLayout()->createBlock($block);
        $resultPage->addContent($content);

        $block = \Webkul\MpWholesale\Block\Adminhtml\Lead\Details\Edit\Tabs::class;
        $left = $resultPage->getLayout()->createBlock($block);
        $resultPage->addLeft($left);
        return $resultPage;
    }
}
