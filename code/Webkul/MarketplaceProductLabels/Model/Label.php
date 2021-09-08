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

namespace Webkul\MarketplaceProductLabels\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Webkul\MarketplaceProductLabels\Api\Data\LabelInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Webkul\MarketplaceProductLabels\Model\ResourceModel\Label as ResourceLabel;
use Webkul\MarketplaceProductLabels\Model\ResourceModel\Label\Collection;
use Webkul\MarketplaceProductLabels\Api\Data\LabelInterface;

class Label extends AbstractModel
{
    /**
     * product label STATUS
     */
    const STATUS_DISAPPROVE = 0;
    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = 2;
    const STATUS_PENDING = 3;

    /**
     * product label POSITION
     */
    const POSITION_TOP_LEFT = 1;
    const POSITION_TOP_RIGHT = 2;
    const POSITION_BOTTOM_LEFT = 3;
    const POSITION_BOTTOM_RIGHT = 4;
    
    /**
     * @var \Webkul\LabelBoard\Api\Data\LabelInterfaceFactory
     */
    protected $labelDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;
    
    /**
     * @var string $_eventPrefix
     */
    protected $_eventPrefix = 'Labelboard_Label';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param LabelInterfaceFactory $labelDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param ResourceLabel $resource
     * @param Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        LabelInterfaceFactory $labelDataFactory,
        DataObjectHelper $dataObjectHelper,
        ResourceLabel $resource,
        Collection $resourceCollection,
        array $data = []
    ) {
        $this->labelDataFactory = $labelDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve Label model with Label data
     * @return labelDataObject
     */
    public function getDataModel()
    {
        $LabelData = $this->getData();
        
        $labelDataObject = $this->labelDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $labelDataObject,
            $LabelData,
            LabelInterface::class
        );
        
        return $labelDataObject;
    }
}
