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

namespace Webkul\MpWholesale\Block\Form;

use Magento\Framework\UrlInterface;

class Register extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

     /**
      * @var UrlInterface
      */
    protected $urlBuilder;

    /**
     * @var Webkul\MpWholesale\Helper\Data
     */
    protected $helper;

     /**
      * @param \Magento\Catalog\Block\Product\Context $context
      * @param \Webkul\MpWholesale\Helper\Data $helper
      * @param UrlInterface $urlBuilder
      * @param array $data
      */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\MpWholesale\Helper\Data $helper,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
         $this->urlBuilder = $urlBuilder;
         $this->helper = $helper;
         parent::__construct($context, $data);
    }

    public function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Create New Wholesaler Account'));
        return parent::_prepareLayout();
    }

    /**
     * Retrieve Wholesaler register form url
     *
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->urlBuilder->getUrl('mpwholesale/account/createpost');
    }

    /**
     * @return string
     */
    public function getWholeSalerRegistrationTerms()
    {
        return $this->helper->getFilterContent();
    }

    /**
     * @return string
     */
    public function getPolicyHeading()
    {
        return $this->helper->getHedaingData();
    }

    /**
     * This function will return json encoded data
     *
     * @param json $data
     * @return object
     */
    public function jsonEncodeData($data)
    {
        return $this->helper->jsonEncodeData($data);
    }
}
