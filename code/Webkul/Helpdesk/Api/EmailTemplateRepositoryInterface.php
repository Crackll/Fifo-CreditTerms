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
interface EmailTemplateRepositoryInterface
{
    /**
     * getEmailCustomVariables This function returns the ticket custom variables for email
     * @param Int $ticketId Ticket Id
     * @return Array ticket custom variables
     */
    public function getEmailCustomVariables($ticketId);
}
