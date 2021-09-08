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

namespace Webkul\MpWholesale\Model;

use Webkul\MpWholesale\Api\Data;
use Webkul\MpWholesale\Api\WholeSaleUserRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpWholesale\Model\ResourceModel\WholeSaleUser as WholeSaleUserResource;
use Webkul\MpWholesale\Model\ResourceModel\WholeSaleUser\CollectionFactory as WholeSaleUserCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class WholeSaleUser deatils Repository
 */
class WholeSaleUserRepository implements WholeSaleUserRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $wholeSaleUserFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $wholeSaleUserCollectionFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param WholeSaleUserResource $resource
     * @param WholeSaleUserFactory $wholeSaleUserFactory
     * @param WholeSaleUserCollection $wholeSaleUserCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        WholeSaleUserResource $resource,
        WholeSaleUserFactory $wholeSaleUserFactory,
        WholeSaleUserCollection $wholeSaleUserCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->wholeSaleUserFactory = $wholeSaleUserFactory;
        $this->wholeSaleUserCollectionFactory = $wholeSaleUserCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save WholeSale User Complete data
     *
     * @param \Webkul\MpWholesale\Api\Data\WholeSaleUserInterface $wholeSaleUser
     * @return WholeSaleUser
     * @throws CouldNotSaveException
     */
    public function save(Data\WholeSaleUserInterface $wholeSaleUser)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $wholeSaleUser->setStoreId($storeId);
        try {
            $this->resource->save($wholeSaleUser);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $wholeSaleUser;
    }

    /**
     * Load WholeSale user Complete data by given Block Identity
     *
     * @param string $id
     * @return WholeSaleUser
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $wholeSaleUser = $this->wholeSaleUserFactory->create();
        $this->resource->load($wholeSaleUser, $id);
        if (!$wholeSaleUser->getEntityId()) {
            throw new NoSuchEntityException(__('Wholesale user with id "%1" does not exist.', $id));
        }
        return $wholeSaleUser;
    }

    /**
     * Delete WholeSale User
     *
     * @param \Webkul\MpWholesale\Api\Data\WholeSaleUserInterface $wholeSaleUser
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\WholeSaleUserInterface $wholeSaleUser)
    {
        try {
            $this->resource->delete($wholeSaleUser);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete WholeSale User by given Block Identity
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
