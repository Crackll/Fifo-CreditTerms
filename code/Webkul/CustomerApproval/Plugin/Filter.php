<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerApproval
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\CustomerApproval\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponentInterface;

class Filter
{
    /**
     * @var $request
     */
    protected $request;

    /**
     * Undocumented function
     *
     * @param \Magento\Framework\App\Request\Http $request
     */

    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Get data provider
     *
     * @return DataProviderInterface
     */
    public function aroundPrepareComponent(
        \Magento\Ui\Component\MassAction\Filter $subject,
        callable $proceed,
        $component
    ) {
        $componentName = $component->getName();
        if ($componentName != 'customer_listing') {
            $proceed($component);
        }
    }
}
