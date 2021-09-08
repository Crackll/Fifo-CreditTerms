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

namespace Webkul\MarketplaceProductLabels\Block;

class ImageLabelOnProduct extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Webkul\MarketplaceProductLabels\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry ;

    /**
     * @var \Webkul\MarketplaceProductLabels\Model\LabelFactory
     */
    protected $labelFactory;

    /**
     * @var id
     */
    protected $id;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MarketplaceProductLabels\Helper\Data $helper
     * @param \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MarketplaceProductLabels\Helper\Data $helper,
        \Webkul\MarketplaceProductLabels\Model\LabelFactory $labelFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Serialize\Serializer\Json $json,
        array $data = []
    ) {
        $this->labelFactory = $labelFactory;
        $this->helper = $helper;
        $this->coreRegistry = $registry;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    /**
     * Return product_label_id attribute of product
     *
     * @return text
     */
    public function getProductLabelIdAttr()
    {
        if (array_key_exists("product_label_id", $this->coreRegistry->registry('product')->getData())) {
            $this->id = $this->coreRegistry->registry('product')->getData()['product_label_id'];
            return $this->coreRegistry->registry('product')->getData()['product_label_id'];
        }
    }

    /**
     * Get Product Entity Id
     *
     * @return Entity Id
     */
    public function getProductId()
    {
        return $this->coreRegistry->registry('product')->getEntityId();
    }

    /**
     * Return base url of media folder
     *
     * @return String
     */
    public function getBaseUrl()
    {
        return $this->helper->getMediaFolder();
    }

    /**
     * Get Product Label
     *
     * @return label
     */
    public function getProductLabels()
    {
        $label = $this->labelFactory->create()->load($this->id);
        return $label;
    }

    /**
     * @param Array $data
     * @return jsonEncode
     */
    public function getJsonEncode($data)
    {
        return $this->json->serialize($data);
    }
}
