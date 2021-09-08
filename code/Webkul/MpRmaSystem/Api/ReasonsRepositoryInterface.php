<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpRmaSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpRmaSystem\Api;

interface ReasonsRepositoryInterface
{

    /**
     * get by id
     *
     * @param int $id
     * @return \Webkul\MpRmaSystem\Model\Reasons
     */
    public function getById($id);
    /**
     * get by id
     *
     * @param int $id
     * @return \Webkul\MpRmaSystem\Model\Reasons
     */
    public function save(\Webkul\MpRmaSystem\Model\Reasons $subject);
    /**
     * get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria);
    /**
     * delete
     *
     * @param \Webkul\MpRmaSystem\Model\Reasons $subject
     * @return boolean
     */
    public function delete(\Webkul\MpRmaSystem\Model\Reasons $subject);
    /**
     * delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
