<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ElasticSearch
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ElasticSearch\Model\ResourceModel;
 
use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;

class Engine implements EngineInterface
{
 
    protected $catalogProductVisibility;
    private $indexScopeResolver;
 
    public function __construct(
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver $indexScopeResolver
    ) {
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->indexScopeResolver = $indexScopeResolver;
    }
 
    public function getAllowedVisibility()
    {
        return $this->catalogProductVisibility->getVisibleInSiteIds();
    }
 
    public function allowAdvancedIndex()
    {
        return false;
    }
 
    public function processAttributeValue($attribute, $value)
    {
        return $value;
    }
 
    public function prepareEntityIndex($index, $separator = ' ')
    {
        return $index;
    }
 
    public function isAvailable()
    {
        return true;
    }
}
