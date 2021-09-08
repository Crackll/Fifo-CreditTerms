<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWholesale\Api;

/**
 * quote interface.
 * @api
 */
interface QuoteRepositoryInterface
{
    /**
     * Create or update a quote.
     *
     * @param \Webkul\MpWholesale\Api\Data\QuoteInterface $quote
     * @return \Webkul\MpWholesale\Api\Data\QuoteInterface
     */
    public function save(\Webkul\MpWholesale\Api\Data\QuoteInterface $quote);

    /**
     * Get quote by quote Id
     *
     * @param int $quoteId
     * @return \Webkul\MpWholesale\Api\Data\QuoteInterface
     */
    public function getById($quoteId);

    /**
     * Delete quote.
     *
     * @param \Webkul\MpWholesale\Api\Data\QuoteInterface $quote
     * @return bool true on success
     */
    public function delete(\Webkul\MpWholesale\Api\Data\QuoteInterface $quote);

    /**
     * Delete quote by ID.
     *
     * @param int $quoteId
     * @return bool true on success
     */
    public function deleteById($quoteId);
}
