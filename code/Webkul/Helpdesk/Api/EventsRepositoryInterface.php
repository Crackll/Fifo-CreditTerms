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
interface EventsRepositoryInterface
{
    /**
     * checkTicketEvent Record the ticket event
     * @param String $actionType Event Type
     * @param Int $ticketId Ticket Id
     * @param Sting $from From
     * @param Sting $to to
     */
    public function checkTicketEvent($actionType, $ticketId, $from, $to);

    /**
     * checkTicketConditionForSla Check ticket condition for ticket rules
     * @param int $ticketId Ticket Id
     * @param int $type Event Id
     */
    public function checkTicketCondition($ticketId, $eventId);

    /**
     * checkCondition Rules for condition check
     * @param String $condition Condition
     * @param String $haystack Haystack
     * @param String $needle Needle
     * @return Booleam
     */
    public function checkCondition($condition, $haystack, $needle);
}
