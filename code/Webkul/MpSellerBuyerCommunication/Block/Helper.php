<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerBuyerCommunication\Block;

use Magento\Framework\Json\Helper\Data as jsonHelper;
use Webkul\Marketplace\Helper\Data as mpHelper;
use Webkul\MpSellerBuyerCommunication\Helper\Data as commHelper;

class Helper extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;
    
    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $_mpHelper;

    /**
     * @var Webkul\MpSellerBuyerCommunication\Helper\Data
     */
    protected $_commHelper;

    /**
     * @param Context                                           $context
     * @param Magento\Framework\Json\Helper\Data                $jsonHelper
     * @param Webkul\Marketplace\Helper\Data                    $mpHelper
     * @param Webkul\MpSellerBuyerCommunication\Helper\Data     $commHelper
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        jsonHelper $jsonHelper,
        mpHelper $mpHelper,
        commHelper $commHelper,
        \Magento\Cms\Helper\Wysiwyg\Images $wysiwygImages = null,
        array $data = []
    ) {
        $this->_jsonHelper = $jsonHelper;
        $this->_mpHelper = $mpHelper;
        $this->_commHelper = $commHelper;
        $this->wysiwygImages = $wysiwygImages ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->create(\Magento\Cms\Helper\Wysiwyg\Images::class);
        parent::__construct($context, $data);
    }

    /**
     * @return object \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->_jsonHelper;
    }

    /**
     * @return object \Webkul\Marketplace\Helper\Data
     */
    public function getMpHelper()
    {
        return $this->_mpHelper;
    }

    /**
     * @return object \Webkul\MpSellerBuyerCommunication\Helper\Data
     */
    public function getCommHelper()
    {
        return $this->_commHelper;
    }
    /**
     * get wysiwyg url
     *
     * @return string
     */
    public function getWysiwygUrl()
    {
        $currentTreePath = $this->wysiwygImages->idEncode(
            \Magento\Cms\Model\Wysiwyg\Config::IMAGE_DIRECTORY
        );
        $url =  $this->getUrl(
            'marketplace/wysiwyg_images/index',
            [
                'current_tree_path' => $currentTreePath
            ]
        );
        return $url;
    }
}
