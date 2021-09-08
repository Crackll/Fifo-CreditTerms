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
use Webkul\MpWholesale\Api\QuoteConRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpWholesale\Model\ResourceModel\Quoteconversation as QuoteconversationResource;
use Webkul\MpWholesale\Model\ResourceModel\Quoteconversation\CollectionFactory as QuoteconversationCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class QuoteConRepository WholeSale
 */
class QuoteConRepository implements QuoteConRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $quoteconversationFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $quoteconversationCollectionFactory;

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
     * @param QuoteconversationResource    $resource
     * @param QuoteconversationFactory     $quoteconversationFactory
     * @param QuoteconversationCollection  $quoteconversationCollectionFactory
     * @param DataObjectHelper  $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        QuoteconversationResource $resource,
        QuoteconversationFactory $quoteconversationFactory,
        QuoteconversationCollection $quoteconversationCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->quoteconversationFactory = $quoteconversationFactory;
        $this->quoteconversationCollectionFactory = $quoteconversationCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Quote Conversation Complete data
     *
     * @param \Webkul\MpWholesale\Api\Data\QuoteConversationInterface $quoteconversation
     * @return Quoteconversation
     * @throws CouldNotSaveException
     */
    public function save(Data\QuoteConversationInterface $quoteconversation)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $quoteconversation->setStoreId($storeId);
        try {
            $this->resource->save($quoteconversation);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $quoteconversation;
    }

    /**
     * Load Quote Conversation Complete data by given Block Identity
     *
     * @param string $id
     * @return Quoteconversation
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $quoteconversation = $this->quoteconversationFactory->create();
        $this->resource->load($quoteconversation, $id);
        if (!$quoteconversation->getEntityId()) {
            throw new NoSuchEntityException(__('Quote Conversation with id "%1" does not exist.', $id));
        }
        return $quoteconversation;
    }

    /**
     * Delete Quote Conversation
     *
     * @param \Webkul\MpWholesale\Api\Data\QuoteConversationInterface $quoteconversation
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\QuoteConversationInterface $quoteconversation)
    {
        try {
            $this->resource->delete($quoteconversation);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Quote Conversation by given Block Identity
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
