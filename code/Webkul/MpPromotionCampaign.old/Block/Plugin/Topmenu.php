<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpPromotionCampaign\Block\Plugin;

use Magento\Framework\Data\Tree\NodeFactory;

class Topmenu
{

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    public $urlInterface;

    /**
     * Node
     *
     * @var \Magento\Framework\Data\Tree\NodeFactory
     */
    public $nodeFactory;

    /**
     * Constructor
     *
     * @param NodeFactory $nodeFactory
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        NodeFactory $nodeFactory,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->urlInterface = $urlInterface;
    }

    /**
     * Before HTML
     *
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param integer $limit
     * @return void
     */
    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        $node = $this->nodeFactory->create(
            [
                'data' => $this->getNodeAsArray(),
                'idField' => 'id',
                'tree' => $subject->getMenu()->getTree()
            ]
        );
        $subject->getMenu()->addChild($node);
    }

    /**
     * Node as Array
     *
     * @return array
     */
    public function getNodeAsArray()
    {
        return [
            'name' => __('Promotion'),
            'id' => 'mppromotion-offers-menu',
            'url' => $this->urlInterface->getUrl('mppromotioncampaign/promotion'),
            'has_active' => false,
            'is_active' => $this->isActive()
        ];
    }
    
    /**
     * Is Active
     *
     * @return boolean
     */
    private function isActive()
    {
        $activeUrls = 'mppromotioncampaign/promotion';
        $currentUrl = $this->urlInterface->getCurrentUrl();
        if (strpos($currentUrl, $activeUrls) !== false) {
            return true;
        }
        return false;
    }
}
