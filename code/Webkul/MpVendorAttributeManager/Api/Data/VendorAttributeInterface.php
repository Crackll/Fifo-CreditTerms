<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Api\Data;

interface VendorAttributeInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const ATTRIBUTE_ID = 'attribute_id';
    const SHOW_IN_FRONT = 'show_in_front';
    const REQUIRED_FIELD = 'required_field';
    const HAS_PARENT = 'has_parent';
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Attribute ID
     *
     * @return int|null
     */
    public function getAttributeId();

    /**
     * Get Show in Front
     *
     * @return int|null
     */
    public function getShowInFront();

    /**
     * Get is required status
     *
     * @return int|null
     */
    public function getRequiredField();

    /**
     * Get Has Parent
     *
     * @return int|null
     */
    public function getHasParent();

    /**
     * Set ID
     *
     * @return int|null
     */
    public function setId($id);

    /**
     * Set Attribute ID
     *
     * @return int|null
     */
    public function setAttributeId($attributeId);

    /**
     * Set Show in Front
     *
     * @return int|null
     */
    public function setShowInFront($show);

    /**
     * Set is required status
     *
     * @return int|null
     */
    public function setRequiredField($required);

    /**
     * Get Has Parent
     *
     * @return int|null
     */
    public function setHasParent($parent);
}
