<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Api\Data;

/**
 * Marketplace Payment interface.
 * @api
 */
interface ActivityInterface
{
    /**#@+
    * Constants for keys of data array. Identical to the name of the getter in snake case
    */
    const ENTITY_ID    = 'id';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Webkul\Helpdesk\Api\Data\TicketsInterface
     */
    public function setId($id);
}
