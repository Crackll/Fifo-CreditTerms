<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */
namespace Webkul\WebApplicationFirewall\Helper;

use Magento\Framework\App\Response\Http;
use Magento\Framework\UrlInterface;
use Magento\Framework\Phrase;
use Webkul\WebApplicationFirewall\Api\ScanHttpResultInterface;

/**
 * WAF ErrorProcessor class
 */
class ErrorProcessor
{

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * @var Data
     */
    protected $helper;
    /**
     * ErrorProcessor constructor.
     *
     * @param Http $response
     * @param Resolver $resolver
     */
    public function __construct(
        \Magento\Framework\App\Response\Http $response,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        Data $helper,
        UrlInterface $url,
        Http $http
    ) {
        $this->pageFactory = $pageFactory;
        $this->_filterProvider = $filterProvider;
        $this->helper = $helper;
        $this->_response = $response;
        $this->http = $http;
        $this->url = $url;
    }

    /**
     * Redirect to selected cms page.
     *
     * @param ScanHttpResultInterface $result
     * @return void
     */
    public function processIpBlockError($result)
    {
        $pageCode = $this->helper->getConfigData('login_security', 'cms_blocked_ip_page');
        $html = '';
        $page = $this->pageFactory->create()->load($pageCode);
        if ($page->isActive()) {
            $html = $this->_filterProvider->getPageFilter()
                ->filter($page->getContent());
        }
        $this->http->setStatusCode(500);
        $this->http->setBody($html);
        return $this->http;
    }
}
