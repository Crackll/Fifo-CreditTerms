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

namespace Webkul\MpRmaSystem\Model;

class ReasonsRepository implements \Webkul\MpRmaSystem\Api\ReasonsRepositoryInterface
{

    protected $modelFactory = null;

    protected $collectionFactory = null;

    /**
     * initialize
     *
     * @param \Webkul\MpRmaSystem\Model\ReasonsFactory $modelFactory
     * @param \Webkul\MpRmaSystem\Model\ResourceModel\Reasons\CollectionFactory
     * $collectionFactory
     * @return void
     */
    public function __construct(
        \Webkul\MpRmaSystem\Model\ReasonsFactory $modelFactory,
        \Webkul\MpRmaSystem\Model\ResourceModel\Reasons\CollectionFactory $collectionFactory
    ) {
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * get by id
     *
     * @param int $id
     * @return \Webkul\MpRmaSystem\Model\Reasons
     */
    public function getById($id)
    {
        $model = $this->modelFactory->create()->load($id);
        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The CMS block with the "%1" ID doesn\'t exist.', $id)
            );
        }
        return $model;
    }

    /**
     * save
     * @param int $id
     * @return \Webkul\MpRmaSystem\Model\Reasons
     */
    public function save(\Webkul\MpRmaSystem\Model\Reasons $subject)
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
     * @param \Webkul\MpRmaSystem\Model\Reasons $subject
     * @return boolean
     */
    public function delete(\Webkul\MpRmaSystem\Model\Reasons $subject)
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
