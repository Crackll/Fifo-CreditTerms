<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplaceProductLabels\Controller\Adminhtml\Label;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultFactory
    ) {
        $this->backendSession = $context->getSession();
        $this->resultFactory = $resultFactory;
        parent::__construct($context);
    }

    /**
     * NewAction Execute function
     *
     * @return \Magento\Framework\Controller\ResultFactory
     */
    public function execute()
    {
        $result = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $result->forward('edit');
        return $result;
    }
}
