<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedCommission
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvancedCommission\ViewModel;

class HelperViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * Get helper singleton
     *
     * @param string $className
     * @return \Magento\Framework\App\Helper\AbstractHelper
     * @throws \LogicException
     */
    public function helper($className)
    {
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->get($className);
        if (false === $helper instanceof \Magento\Framework\App\Helper\AbstractHelper) {
            throw new \LogicException($className . ' doesn\'t extends Magento\Framework\App\Helper\AbstractHelper');
        }
        return $helper;
    }
}
