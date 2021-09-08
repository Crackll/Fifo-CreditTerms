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

namespace Webkul\MarketplaceProductLabels\Controller\Check;

use Magento\Framework\App\Action\Context;
use Webkul\MarketplaceProductLabels\Helper\Data as LabelHelper;
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\MarketplaceProductLabels\Model\LabelFactory;

class Categorylabel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Webkul\MarketplaceProductLabels\Helper\Data
     */
    protected $labelHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var Webkul\MarketplaceProductLabels\Model\LabelFactory
     */
    protected $labelFactory;
    
    /**
     * @param Context $context
     * @param LabelHelper $labelHelper
     * @param JsonFactory $jsonFactory
     * @param LabelFactory $labelFactory
     */
    public function __construct(
        Context $context,
        LabelHelper $labelHelper,
        JsonFactory $jsonFactory,
        LabelFactory $labelFactory
    ) {
        $this->labelFactory=$labelFactory;
        $this->labelHelper = $labelHelper;
        $this->jsonFactory = $jsonFactory;
        parent::__construct($context);
    }

    /**
     *
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        $info = [];
        $helper = $this->labelHelper;
        $id = $this->getRequest()->getParam('id');
        
        $collection = $this->labelFactory->create()->getCollection()
                        ->addFieldToFilter('id', $id)
                        ->addFieldToFilter('status', 1);
        
        $collection = $collection->getLastItem();
        $result = $this->jsonFactory->create();
        $result->setData($collection);
        return $result;
    }
}
