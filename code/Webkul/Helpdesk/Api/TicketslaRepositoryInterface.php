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
interface TicketslaRepositoryInterface
{
    /**
     * applyReplySLAToTicket Apply Sla on ticket during reply added
     * @param Int $ticketId Ticket Id
     * @param Int $slaId Sla Id
     */
    public function applyReplySLAToTicket($ticketId, $slaId);

    /**
     * applySLAToTicket Apply Sla on ticket during ticket creation
     * @param Int $ticketId Ticket Id
     * @param Int $slaId Sla Id
     */
    public function applySLAToTicket($ticketId, $slaId);

    /**
     * calculateTime Remaining time for resolve and respond ticket
     * @param Array $data SLA details
     * @return Datatime $returnTime Resolve and Respond ticket
     */
    public function calculateTime($data);

    /**
     * getTicketLastReplyDatails Return Last ticket reply
     * @param Int $ticketId Ticket Id
     */
    public function getTicketLastReplyDatails($ticketId);

    /**
     * getTicketResponseTime Return ticket response time
     * @param Int $ticketId Ticket Id
     * @return String Response time
     */
    public function getTicketResponseTime($ticketId);

    /**
     * getTicketResolveTime Return ticket Resolve time
     * @param Int $ticketId Ticket Id
     * @return String Resolve time
     */
    public function getTicketResolveTime($ticketId);

    /**
     * getTicketOffsetById Return timezone offset
     * @param Int $ticketId Ticket Id
     * @return String offset time
     */
    public function getTicketOffsetById($ticketId);
}
