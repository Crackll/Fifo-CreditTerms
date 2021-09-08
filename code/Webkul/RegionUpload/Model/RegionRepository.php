<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_RegionUpload
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\RegionUpload\Model;

class RegionRepository implements \Webkul\RegionUpload\Api\RegionRepositoryInterface
{

    protected $modelFactory = null;

    protected $collectionFactory = null;

    /**
     *
     * @param \Magento\Directory\Model\RegionFactory $modelFactory
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Directory\Model\RegionFactory $modelFactory,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $collectionFactory
    ) {
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * get by id
     *
     * @param int $id
     * @return Magento\Directory\Model\Region
     */
    public function getById($id)
    {
        $model = $this->modelFactory->create()->load($id);
        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('Region with "%1" ID doesn\'t exist.', $id)
            );
        }
        return $model;
    }

    /**
     * get by id
     *
     * @param int $id
     * @return Magento\Directory\Model\Region
     */
    public function save(\Magento\Directory\Model\Region $subject)
    {
        try {
            $subject->save();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($exception->getMessage()));
        }
         return $subject;
    }

    /**
     * get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria)
    {
        $collection = $this->collectionFactory->create();
         return $collection;
    }

    /**
     * delete
     *
     * @param Magento\Directory\Model\Region $subject
     * @return boolean
     */
    public function delete(\Magento\Directory\Model\Region $subject)
    {
        try {
            $subject->delete();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
