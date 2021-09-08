<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRewardSystem\Plugin\Helper;

class Data
{
    /**
     * function to run to change the return data of afterIsSeller.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return bool
     */
    public function afterGetControllerMappedPermissions(
        \Webkul\Marketplace\Helper\Data $helperData,
        $result
    ) {
        $result['mprewardsystem/account/saveprice'] = 'mprewardsystem/account/index';
        $result['mprewardsystem/account/savecartrecords'] = 'mprewardsystem/account/cartrecord';
        $result['mprewardsystem/account/massdelete'] = 'mprewardsystem/account/cartrecord';
        $result['mprewardsystem/account/updatecartrule'] = 'mprewardsystem/account/cartrecord';
        $result['mprewardsystem/account/deletecartrule'] = 'mprewardsystem/account/cartrecord';
        $result['mprewardsystem/product/massAssign'] = 'mprewardsystem/product/index';
        $result['mprewardsystem/category/massAssign'] = 'mprewardsystem/category/index';
        return $result;
    }
}
