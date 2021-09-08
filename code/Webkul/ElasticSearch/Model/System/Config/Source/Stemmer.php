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
class Stemmer implements \Magento\Framework\Option\ArrayInterface
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
        array_push($options, ['label' => __("english"), 'value' => 'english']);
        array_push($options, ['label' => __("arabic"), 'value' => 'arabic']);
        array_push($options, ['label' => __("armenian"), 'value' => 'armenian']);
        array_push($options, ['label' => __("basque"), 'value' => 'basque']);
        array_push($options, ['label' => __("bengali"), 'value' => 'bengali']);
        array_push($options, ['label' => __("light bengali"), 'value' => 'light_bengali']);
        array_push($options, ['label' => __("brazilian"), 'value' => 'brazilian']);
        array_push($options, ['label' => __("bulgarian"), 'value' => 'bulgarian']);
        array_push($options, ['label' => __("catalan"), 'value' => 'catalan']);
        array_push($options, ['label' => __("czech"), 'value' => 'czech']);
        array_push($options, ['label' => __("danish"), 'value' => 'danish']);
        array_push($options, ['label' => __("dutch"), 'value' => 'dutch']);
        array_push($options, ['label' => __("finnish"), 'value' => 'finnish']);
        array_push($options, ['label' => __("french"), 'value' => 'french']);
        array_push($options, ['label' => __("galician"), 'value' => 'galician']);
        array_push($options, ['label' => __("german"), 'value' => 'german']);
        array_push($options, ['label' => __("greek"), 'value' => 'greek']);
        array_push($options, ['label' => __("hindi"), 'value' => 'hindi']);
        array_push($options, ['label' => __("hungarian"), 'value' => 'hungarian']);
        array_push($options, ['label' => __("indonesian"), 'value' => 'indonesian']);
        array_push($options, ['label' => __("irish"), 'value' => 'irish']);
        array_push($options, ['label' => __("italian"), 'value' => 'italian']);
        array_push($options, ['label' => __("sorani"), 'value' => 'sorani']);
        array_push($options, ['label' => __("latvian"), 'value' => 'latvian']);
        array_push($options, ['label' => __("lithuanian"), 'value' => 'lithuanian']);
        array_push($options, ['label' => __("norwegian"), 'value' => 'norwegian']);
        array_push($options, ['label' => __("light_nynorsk"), 'value' => 'light_nynorsk']);
        array_push($options, ['label' => __("portuguese"), 'value' => 'portuguese']);

        return $options;
    }
}
