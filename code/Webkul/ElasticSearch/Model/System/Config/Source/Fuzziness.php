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
class Fuzziness implements \Magento\Framework\Option\ArrayInterface
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
        array_push($options, ['label' => __("Disable"), 'value' => 0]);
        array_push($options, ['label' => __("Level 1"), 'value' => 1]);
        array_push($options, ['label' => __("Level 2"), 'value' => 2]);
        return $options;
    }
}
