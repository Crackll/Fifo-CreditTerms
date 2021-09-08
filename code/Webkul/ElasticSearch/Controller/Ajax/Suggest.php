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
namespace Webkul\ElasticSearch\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Search\Model\AutocompleteInterface;
use Magento\Framework\Controller\ResultFactory;

class Suggest extends Action
{

    /**
     * @var \Webkul\ElasticSearch\Helper\Data
     */
    protected $helper;

    /**
     * @var Webkul\ElasticSearch\Model\Adapter\ElasticAdapter
     */
    protected $elasticAdapter;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $categoryFactory;
    
    /**
     * @var Magento\Cms\Helper\Page
     */
    protected $cmsPage;


    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Search\Model\AutocompleteInterface $autocomplete
     */
    public function __construct(
        Context $context,
        \Webkul\ElasticSearch\Helper\Data $helper,
        \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter $elasticAdapter,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Cms\Helper\Page $cmsPage,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        $this->elasticAdapter = $elasticAdapter;
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->cmsPage = $cmsPage;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('q', false)) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_url->getBaseUrl());
            return $resultRedirect;
        }
        $suggestQuery = $this->getSuggestQuery();
        $responseData = [];
        $this->elasticAdapter->refreshIndex([ "index" => $this->helper->getIndexName('catalog')]);
        $result = $this->elasticAdapter->search($suggestQuery);
        $this->elasticAdapter->refreshIndex([ "index" => $this->helper->getIndexName('cms')]);
        $resultCms = $this->elasticAdapter->search($this->getSuggestQueryForCms());

        if (isset($result['hits'])) {
            
            $suggetions = $this->getSuggestion($result);
            $total = count($suggetions);
            if ($total > 0) {
                foreach ($suggetions as $key => $s) {
                    if (strpos($s['_index'], "catalog-category") !== false) {
                        $catId = $s['_id'];
                        $category = $this->categoryFactory->create();
                        $category->load($catId);
                        if ($category->getLevel() == 0 || $category->getLevel() == 1) {
                            continue;
                        }
                        $title = $s['_source']['name'];
                        $path = $category->getPath();
                        if ($path) {
                            $path = explode("/", $path);
                            $categoryNames = [];
                            foreach ($path as $pathId) {
                                if ($pathId == 1 || $pathId == 2) {
                                    continue;
                                }
                                $childCat = $this->categoryFactory->create()->load($pathId);
                                $categoryNames[] = $childCat->getName();
                            }
                            $title = implode(" > ", $categoryNames);
                        }
                        $responseData[]['title'] = $s['_source']['name']." <a class='wkcatsuggest' href='".$category->getUrl()."'> in ".$title."</a>";
                        
                    } elseif (strpos($s['_index'], "catalog") !== false) {
                        $product = $this->productFactory->create()->load($s["_id"]);
                        if ($product->getStatus() == 1 && $product->getAttributeText('visibility') == "Catalog, Search") {
                            $responseData[]['title'] = sprintf("<a href='%s'>%s</a>", $product->getProductUrl(), $s['_source']['name']);
                        }
                    }
                }
            }
        }

        if (isset($resultCms['hits'])) {
            
            $suggetions = $this->getSuggestion($resultCms);
            $total = count($suggetions);
            if ($total > 0) {
                foreach ($suggetions as $key => $s) {
                    $pageId = $s['_id'];
                    $pageUrl = $this->cmsPage->getPageUrl($pageId);
                    $responseData[]['title'] = $s['_source']['title']." <a class='wkcatsuggest' href='".$pageUrl."'> in pages</a>";
                }
            }
        }
       
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }

    private function getSuggestion($result)
    {
        if (isset($result['suggest'])) {
            if ($result['suggest']['search-suggetion']) {
                if (isset($result['suggest']['search-suggetion'][0]['options'])) {
                    
                    return $result['suggest']['search-suggetion'][0]['options'];
                }
            }
            
        } elseif ($result['hits']) {
            return $result['hits']['hits'];
        }

        return [];
    }

    /**
     * create suggest query for auto-complete
     *
     * @return array
     */
    private function getSuggestQuery()
    {
        $queryText = $this->getRequest()->getParam('q');
        $suggestQuery = [
            'index' => sprintf("%s,%s", $this->helper->getIndexName('catalog'), $this->helper->getIndexName('catalog-category')),
            'body' => [
                "_source" => ["name", "status"],
                'suggest' => [
                    'search-suggetion' => [
                        "prefix" => $queryText,
                        "completion" => [
                            "field" => "name_suggest",
                            "size" => 12,
                            "fuzzy" => [
                                "fuzziness" => $this->helper->getFuzziness()
                                ]
                        ]
                    ]
                ]
            ]
        ];
        return $suggestQuery;
    }

    /**
     * create suggest query for auto-complete
     *
     * @return array
     */
    private function getSuggestQueryForCms()
    {
        $queryText = $this->getRequest()->getParam('q');
        $suggestQuery = [
            'index' => $this->helper->getIndexName('cms'),
            'body' => [
                "_source" => ["title"],
                'suggest' => [
                    'search-suggetion' => [
                        "prefix" => $queryText,
                        "completion" => [
                            "field" => "title_suggest",
                            "size" => 3,
                            "fuzzy" => [
                                "fuzziness" => $this->helper->getFuzziness()
                            ]
                        ]
                    ]
                ]
               
            ]
        ];
        return $suggestQuery;
    }
}
