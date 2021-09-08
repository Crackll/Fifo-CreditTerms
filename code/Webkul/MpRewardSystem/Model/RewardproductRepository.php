<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpRewardSystem
 * @author Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\MpRewardSystem\Model;

use Webkul\MpRewardSystem\Api\Data;
use Webkul\MpRewardSystem\Api\RewardproductRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewardproduct as RewardProductResource;
use Webkul\MpRewardSystem\Model\ResourceModel\Rewardproduct\CollectionFactory as RewardProductCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class TimeslotConfigRepository
 *
 */
class RewardproductRepository implements RewardproductRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $rewardProductFactory;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @param RewardProductResource $resource
     * @param RewardproductFactory $rewardProductFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        RewardProductResource $resource,
        RewardproductFactory $rewardProductFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->rewardProductFactory = $rewardProductFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Save reward product Complete data
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewardproductInterface $rewardProduct
     * @return PreorderComplete
     * @throws CouldNotSaveException
     */
    public function save(Data\RewardproductInterface $rewardProduct)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $rewardProduct->setStoreId($storeId);
        try {
            $this->resource->save($rewardProduct);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $rewardProduct;
    }

    /**
     * Load Reward Product Complete data by given Block Identity
     *
     * @param string $id
     * @return rewardProduct
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $rewardProduct = $this->rewardProductFactory->create();
        $this->resource->load($rewardProduct, $id);
        if (!$rewardProduct->getEntityId()) {
            throw new NoSuchEntityException(__('Reward Product with id "%1" does not exist.', $id));
        }
        return $rewardProduct;
    }

    /**
     * Delete RewardProduct
     *
     * @param \Webkul\MpRewardSystem\Api\Data\RewardproductInterface $rewardProduct
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\RewardproductInterface $rewardProduct)
    {
        try {
            $this->resource->delete($rewardProduct);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete RewardProduct by given Block Identity
     *
     * @param string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
