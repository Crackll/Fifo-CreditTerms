<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\WebApplicationFirewall\Api\Data;

interface AdminLoginSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get AdminLogin list.
     * @return \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface[]
     */
    public function getItems();

    /**
     * Set username list.
     * @param \Webkul\WebApplicationFirewall\Api\Data\AdminLoginInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
