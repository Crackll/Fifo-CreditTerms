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
interface QuoteConRepositoryInterface
{
    /**
     * Create or update a quote.
     *
     * @param \Webkul\MpWholesale\Api\Data\QuoteConversationInterface $quoteConversation
     * @return \Webkul\MpWholesale\Api\Data\QuoteConversationInterface
     */
    public function save(\Webkul\MpWholesale\Api\Data\QuoteConversationInterface $quoteConversation);

    /**
     * Get quote conversation by quoteConversation Id
     *
     * @param int $id
     * @return \Webkul\MpWholesale\Api\Data\QuoteConversationInterface
     */
    public function getById($id);

    /**
     * Delete quote conversation.
     *
     * @param \Webkul\MpWholesale\Api\Data\QuoteConversationInterface $quoteConversation
     * @return bool true on success
     */
    public function delete(\Webkul\MpWholesale\Api\Data\QuoteConversationInterface $quoteConversation);

    /**
     * Delete quote conversation by ID.
     *
     * @param int $id
     * @return bool true on success
     */
    public function deleteById($id);
}
