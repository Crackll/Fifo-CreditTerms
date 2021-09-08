<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Block;

/**
 * Webkul MpWalletSystem Block
 */
class Walletsystem extends \Magento\Framework\View\Element\Template
{
    /**
     * Initialize dependencies
     *
     * @param \Magento\Catalog\Block\Product\Context    $context
     * @param \Magento\Framework\ObjectManagerInterface $helperFactory
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\ObjectManagerInterface $helperFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperFactory = $helperFactory;
    }

    /**
     * Use to get current url.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        // Give the current url of recently viewed page
        return $this->_urlBuilder->getCurrentUrl();
    }
    
    /**
     * GetIsSecure check is secure or not
     *
     * @return boolean
     */
    public function getIsSecure()
    {
        return $this->getRequest()->isSecure();
    }

    /**
     * Get parameters from request
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * Get helper singleton
     *
     * @param  string $className
     * @return \Magento\Framework\App\Helper\AbstractHelper
     * @throws \LogicException
     */
    public function helper($className)
    {
        $helper = $this->helperFactory->get($className);
        if (false === $helper instanceof \Magento\Framework\App\Helper\AbstractHelper) {
            throw new \LogicException($className . ' doesn\'t extends Magento\Framework\App\Helper\AbstractHelper');
        }
        return $helper;
    }
}
