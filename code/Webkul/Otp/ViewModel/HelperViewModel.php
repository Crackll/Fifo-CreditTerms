<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Otp
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Otp\ViewModel;

class HelperViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magneto\Checkout\Model\CompositeConfigProvider
     */
    private $configProvider;

    /**
     * @param \Magento\Checkout\Model\CompositeConfigProvider $configProvider
     */
    public function __construct(
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider
    ) {
        $this->configProvider = $configProvider;
    }

    /**
     * Get Checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->configProvider->getConfig();
    }

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
