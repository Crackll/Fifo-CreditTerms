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
class Filters implements \Magento\Framework\Option\ArrayInterface
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
        array_push($options, ['label' => __("Elision Filter"), 'value' => 'elision']);
        array_push($options, ['label' => __("Lowercase Filter"), 'value' => 'lowercase']);
        array_push($options, ['label' => __("Synonym Filter"), 'value' => 'synonym']);
        array_push($options, ['label' => __("Stop Word Filter"), 'value' => 'stop']);
        return $options;
    }
}
