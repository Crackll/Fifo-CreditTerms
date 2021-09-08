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
use Webkul\MarketplaceProductLabels\Model\ImageUploaderFactory;
use Webkul\MarketplaceProductLabels\Helper\Data as LabelHepler;

class Save extends Action
{

    /**
     * @var \Webkul\MarketplaceProductLabels\Model\LabelFactory
     */
    protected $labelFactory;

    /**
     * @var \Webkul\MarketplaceProductLabels\Model\ImageUploaderFactory
     */
    protected $imageUploaderFactory;

    /**
     * @var LabelHepler
     */
    protected $labelhelper;

    /**
     * @param Action\Context $context
     * @param \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory
     * @param ImageUploaderFactory $ImageUploaderFactory
     * @param LabelHepler $LabelHepler
     */
    public function __construct(
        Action\Context $context,
        \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory,
        ImageUploaderFactory $ImageUploaderFactory,
        LabelHepler $LabelHepler
    ) {
        $this->labelFactory = $labelFactory;
        $this->imageUploaderFactory = $ImageUploaderFactory;
        $this->labelhelper = $LabelHepler;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MarketplaceProductLabels::seller_label_grid');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->getRequest()->isPost()) {
            $this->messageManager->addError(__("Something went wrong"));
            return $resultRedirect->setPath('*/*/');
        }
        try {
            $labelData = $this->getRequest()->getParams();
            $labelData['label_name'] = strip_tags(trim($labelData['label_name']));
            if (isset($labelData['labels'][0]['name'])) {
                $labelData['image_name'] = $labelData['labels'][0]['name'];
            }
            
            $labelModel = $this->labelFactory->create();
            $applicantId = $this->getRequest()->getParam('id');
            if ($this->getRequest()->getParam('id')) {
                $labelModel->load($this->getRequest()->getParam('id'));
            }
            
            try {
                $this->imageUploaderFactory->create()->moveFileFromTmp(
                    $labelData['labels'][0]['name'],
                    $labelData['labels'][0]['url']
                );
            } catch (\Exception $e) {
                if ($applicantId) {
                    $labelData['image_name'] = $labelModel['labels'][0]['name'];
                } else {
                    $this->messageManager->addError(__($e->getMessage()));
                    return $resultRedirect->setPath('*/*/');
                }
            }
            if (!isset($labelData['seller_id'])) {
                $labelData['seller_id'] = 0;
            }
            $labelDimension = [
                'label_width_productpage',
                'label_height_productpage',
                'label_width_categorypage',
                'label_height_categoryage'
            ];
            $defaultLabelDim = [
                'defaultWidth_productPage',
                'defaultHeight_productPage',
                'defaultWidth_categoryPage',
                'defaultWidth_categoryPage'
            ];

            $count = 0;
            foreach ($labelDimension as $labelDim) {
                if (empty($labelData[$labelDim])) {
                    $labelData[$labelDim] = $this->labelhelper->getConfigData($defaultLabelDim[$count]);
                    $count++;
                }
            }
            $labelModel->setData($labelData);
            $labelModel->save();
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            return $resultRedirect->setPath('*/*/');
        }
        $this->messageManager->addSuccess(__("Product Label saved successfully"));
        return $resultRedirect->setPath('*/*/');
    }
}
