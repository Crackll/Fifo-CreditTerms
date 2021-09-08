<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ElasticSearch
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ElasticSearch\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class DidYouMean extends Template
{
    /**
     * @var \Webkul\ElasticSearch\Data\Helper
     */
    protected $_helper;

    /**
     * constructor
     *
     * @param \Webkul\ElasticSearch\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Data $catalogSearchData
     */
    public function __construct(
        Context $context,
        \Webkul\ElasticSearch\Helper\Data $helper,
        array $data = []
    ) {
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * get did you mean text
     *
     * @return string
     */
    public function getDidYouMeanText()
    {
        $result = '';
        if ($this->_helper->canUseElastic()) {
            $actualQuery = $this->getRequest()->getParam("actual_param");
            if ($actualQuery) {
                $result = __(" search instead for:").sprintf(
                    " <a href='%s'>%s</a>",
                    $this->getActualTextUrl($actualQuery),
                    $this->escapeHtml($actualQuery)
                );
            }
        }

        return $result;
    }

    /**
     * get actual url
     *
     * @param string $actualQuery
     * @return string
     */
    public function getActualTextUrl($actualQuery)
    {
        return $this->getUrl("catalogsearch/result/index", ["qq" => $actualQuery]);
    }
}
