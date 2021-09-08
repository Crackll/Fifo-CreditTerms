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

interface FrontendTwoStepAuthSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get FrontendTwoStepAuth list.
     * @return \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface[]
     */
    public function getItems();

    /**
     * Set entity_id list.
     * @param \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
