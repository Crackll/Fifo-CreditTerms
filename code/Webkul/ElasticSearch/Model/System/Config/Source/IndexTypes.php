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
 * Config source for index type
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class IndexTypes implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return option array
     *
     * @param bool $addEmpty
     * @return array
     */
    public function toOptionArray($addEmpty = true)
    {
        $options = [];
        array_push($options, ['label' => __("Product"), 'value' => 'product']);
        array_push($options, ['label' => __("Category"), 'value' => 'category']);
        array_push($options, ['label' => __("Pages"), 'value' => 'page']);
        return $options;
    }
}
