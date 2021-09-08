<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\ViewModel;

use Magento\Framework\Json\Helper\Data as JsonHelper;

class MpAuction implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * jsonHelper
     *
     * @var jsonHelper
     */
    public $jsonHelper;
    
     /**
      * jsonHelper
      *
      * @var jsonHelper
      */
    public $marketplaceHelper;
    /**
     * MpAuction
     *
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        JsonHelper $jsonHelper
    ) {
        $this->marketplaceHelper = $marketplaceHelper;
        $this->jsonHelper = $jsonHelper;
    }
    /**
     * getMarketplce Helper
     *
     * @return markeptlaceHelper
     */
    public function getMarketplaceHelper()
    {
        return $this->marketplaceHelper;
    }
    
    /**
     * getJsonHelper
     *
     * @return josnHelper
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
