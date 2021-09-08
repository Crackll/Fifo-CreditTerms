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
namespace Webkul\ElasticSearch\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Store\Model\App\Emulation;

class SearchResultPreDispatch implements ObserverInterface
{

    /**
     * @var \Webkul\ElasticSearch\Data\Helper
     */
    protected $_helper;

     /**
      * @var Webkul\ElasticSearch\Model\Adapter\ElasticAdapter
      */
    protected $elasticAdapter;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    public function __construct(
        \Webkul\ElasticSearch\Model\Adapter\ElasticAdapter $elasticAdapter,
        \Webkul\ElasticSearch\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_helper = $helper;
        $this->elasticAdapter = $elasticAdapter;
        $this->request = $request;
    }

    public function execute(Observer $observer)
    {
        if ($this->_helper->canUseElastic()) {
            $qq = $this->request->getParam("qq");
            if (!$qq) {
                $query = $this->request->getParam("q");
                if ($query) {
                    $didYouMean = $this->getDidYouMeanText($query);
                    if ($didYouMean != $query) {
                        $this->request->setParam("q", $didYouMean);
                        $this->request->setParam("actual_param", $query);
                    }
                }
            } else {
                $this->request->setParam("q", $qq);
            }
        }
    }

    /**
     * get did you mean text
     *
     * @param string $query
     *
     * @return string
     */
    private function getDidYouMeanText($query)
    {
        $searchQuery = $this->getSuggestQuery($query);
        $result = $this->elasticAdapter->search($searchQuery);
        if (!isset($result['errors']) && isset($result['suggest'])) {
            $options = $result['suggest']['simple_phrase'][0]['options'];
            if (count($options) > 0) {
                return $options[0]['text'];
            }
        }
        
        return $query;
    }

    /**
     * get did you mean query
     * @param string $query
     * @return array
     */
    private function getSuggestQuery($query)
    {
        return $suggestQuery = [
            'index' => sprintf("%s", $this->_helper->getIndexName('catalog')),
            'type' => 'product',
            'body' => [
                'suggest' => [
                    'text' => $query,
                    'simple_phrase' => [
                        'phrase' => [
                            'field' => 'name_autocomplete',
                            'size' => 2,
                            'gram_size' => 3,
                            "direct_generator" => [
                                [
                                    "field" =>  "name_autocomplete",
                                    "suggest_mode" => "always"
                                ]
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }
}
