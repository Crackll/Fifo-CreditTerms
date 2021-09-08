<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_ElasticSearch
 * @author Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\ElasticSearch\Model\System\Config\Source;

/**
 * Config source
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class MultiSearchFields implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * helper
     *
     * @var \Webkul\ElasticSearch\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Webkul\ElasticSearch\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }
    
    /**
     * Return option array
     *
     * @param bool $addEmpty
     * @return array
     */
    public function toOptionArray($addEmpty = true)
    {
        $searchFields = $this->_helper->getSearchableAttrbutes();
        $options = [];
        foreach ($searchFields as $attr) {
            array_push($options, ['label' => $attr["code"], 'value' => $attr["code"]]);
        }
        return $options;
    }
}
