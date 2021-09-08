<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RegionUpload
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\RegionUpload\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class MapConfigProvider implements ConfigProviderInterface
{
   /**
    * @var \Webkul\RegionUpload\Helper\MapData
    */
    protected $helperData;

    /**
     * Constructor.
     * @param \Webkul\RegionUpload\Helper\MapData     $helperData
     */

    public function __construct(
        \Webkul\RegionUpload\Helper\MapData $helperData
    ) {
        $this->helperData = $helperData;
    }
    /**
     * set data in window.checkout.config for checkout page.
     *
     * @return array $options
     */
    public function getConfig()
    {
        $options = [
            'map' => []
        ];
        $options['map']['status'] = $this->helperData->getGoogleMapStatus();
        $options['map']['api_key'] = $this->helperData->getApiKey();
        return $options;
    }
}
