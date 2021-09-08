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
interface AttachmentRepositoryInterface
{
    /**
     * saveActivity Save Helpdesk activity
     * @param int $ticket_id Entity Id
     * @param String $thread_id Entity Name
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveAttachment($ticket_id, $thread_id);
}
