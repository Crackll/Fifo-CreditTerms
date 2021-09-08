<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_RegionUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */


namespace Webkul\RegionUpload\Api;

interface RegionRepositoryInterface
{

    /**
     * get by id
     *
     * @param int $id
     * @return Magento\Directory\Model\Region
     */
    public function getById($id);
    /**
     * get by id
     *
     * @param int $id
     * @return Magento\Directory\Model\Region
     */
    public function save(\Magento\Directory\Model\Region $subject);
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
     * @param Magento\Directory\Model\Region $subject
     * @return boolean
     */
    public function delete(\Magento\Directory\Model\Region $subject);
    /**
     * delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
