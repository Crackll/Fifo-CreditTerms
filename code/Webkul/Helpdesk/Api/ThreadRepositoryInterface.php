<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Helpdesk\Api;

/**
 * @api
 * @since 100.0.2
 */
interface ThreadRepositoryInterface
{
    /**
     * Create product
     *
     * @param int $ticketId
     * @param mixed $wholedata
     * @return int $ticketId
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function createThread($ticketId, $wholedata);

    /**
     * deleteThreads Delete thread from ticket
     * @param Int $ticketId  Ticket Id
     */
    public function deleteThreads($ticketId);

    /**
     * getTicketIdByThreadId Return ticket id by thread id
     * @param Int $threadId Thread id
     */
    public function getTicketIdByThreadId($threadId);
}
