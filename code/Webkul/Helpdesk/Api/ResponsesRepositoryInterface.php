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
interface ResponsesRepositoryInterface
{
    /**
     * applyResponseToTicket This function is resposible for applying the action to the ticket
     * @param Int $ticketId Ticket Id
     * @param JSON $response Response action data
     */
    public function applyResponseToTicket($ticketId, $response);
}
