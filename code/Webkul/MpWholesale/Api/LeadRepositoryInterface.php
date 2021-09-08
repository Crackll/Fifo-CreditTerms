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
 * LeadRepository WholeSale interface.
 * @api
 */
interface LeadRepositoryInterface
{
    /**
     * Create or update a lead.
     *
     * @param \Webkul\MpWholesale\Api\Data\LeadInterface $lead
     * @return \Webkul\MpWholesale\Api\Data\LeadInterface
     */
    public function save(\Webkul\MpWholesale\Api\Data\LeadInterface $lead);

    /**
     * Get Lead by leadId
     *
     * @param int $leadId
     * @return \Webkul\MpWholesale\Api\Data\LeadInterface
     */
    public function getById($leadId);

    /**
     * Delete Lead.
     *
     * @param \Webkul\MpWholesale\Api\Data\LeadInterface $lead
     * @return bool true on success
     */
    public function delete(\Webkul\MpWholesale\Api\Data\LeadInterface $lead);

    /**
     * Delete lead by ID.
     *
     * @param int $leadId
     * @return bool true on success
     */
    public function deleteById($leadId);
}
