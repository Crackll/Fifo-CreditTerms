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
interface MailfetchRepositoryInterface
{
    /**
     * fetchMail Fetch the email and create tickets and threads
     * @param Int $connectEmailId connect Email Id
     * @return Int Fetch email count
     */
    public function fetchMail($connectEmailId);

    /**
     * processMail Process the mail the create ticket from them
     * @param Object $message Mail message object
     * @param Int $connectEmailId connect Email Id
     * @return Int Fetch email count
     */
    public function processMail($message, $connectEmailId);
}
