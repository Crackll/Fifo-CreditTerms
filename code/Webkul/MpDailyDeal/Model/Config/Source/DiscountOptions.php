<?php

namespace Webkul\MpDailyDeal\Model\Config\Source;

/**
 * Webkul MpDailyDeal List Config Source Model
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 *
 */

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class DiscountOptions extends AbstractSource
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Registry $registry
    ) {
        $this->request = $request;
        $this->coreRegistry = $registry;
    }

    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $proType = $this->request->getParam('type');
            if ($proType == '') {
                $product = $this->coreRegistry->registry('product');
                if ($product) {
                    $proType = $product->getTypeId();
                }
            }
            if ($proType != 'bundle') {
                $this->_options[] = ['label' => __('Fixed'), 'value' => 'fixed'];
            }
            $this->_options[] = ['label' => __('Percent'), 'value' => 'percent'];
        }
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
