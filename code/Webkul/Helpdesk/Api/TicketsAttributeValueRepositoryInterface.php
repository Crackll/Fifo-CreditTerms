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
interface TicketsAttributeValueRepositoryInterface
{
    /**
     * saveTicketAttributeValues
     *
     * @return int $ticketId
     * @param mixed $wholedata
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveTicketAttributeValues($ticketId, $wholedata);

    /**
     * editTicketAttributeValues save ticket custom attribute values
     * @param Array $wholedata Post request data
     * @return [type]            [description]
     */
    public function editTicketAttributeValues($wholedata);
}
