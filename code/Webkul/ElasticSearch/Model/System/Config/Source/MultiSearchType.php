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
class MultiSearchType implements \Magento\Framework\Option\ArrayInterface
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
        array_push($options, ['label' => __("Best Fields"), 'value' => 'best_fields']);
        array_push($options, ['label' => __("Most Fields"), 'value' => 'most_fields']);
        array_push($options, ['label' => __("Cross Fields"), 'value' => 'cross_fields']);
        array_push($options, ['label' => __("Phrase"), 'value' => 'phrase']);
        array_push($options, ['label' => __("Phrase Prefix"), 'value' => 'phrase_prefix']);
        return $options;
    }
}
