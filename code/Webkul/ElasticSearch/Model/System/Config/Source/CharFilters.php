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
 * Config source for search type
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class CharFilters implements \Magento\Framework\Option\ArrayInterface
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
        array_push($options, ['label' => __("Html Strip Char Filter"), 'value' => 'html_strip']);
        array_push($options, ['label' => __("Mapping Filter"), 'value' => 'mapping']);
        array_push($options, ['label' => __("Pattern Replace Filter"), 'value' => 'pattern_replace']);
        return $options;
    }
}
