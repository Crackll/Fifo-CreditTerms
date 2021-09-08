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

use Magento\Framework\Controller\ResultFactory;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Webkul\MarketplaceProductLabels\Model\LabelFactory
     */
    protected $labelFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory
    ) {
        $this->labelFactory = $labelFactory;
        parent::__construct($context);
    }

    /**
     * Label Delete Action
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $labelId = $this->getRequest()->getParams();
        
        try {
            $model = $this->labelFactory->create()->load($labelId["id"]);
            $model->delete();
            $this->messageManager->addSuccessMessage(__("Label deleted succesfully."));
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Error occurred while deleting label."));
        }

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}
