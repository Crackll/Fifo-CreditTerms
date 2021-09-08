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
use Webkul\MpWholesale\Api\QuoteRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpWholesale\Model\ResourceModel\Quotes as QuotesResource;
use Webkul\MpWholesale\Model\ResourceModel\Quotes\CollectionFactory as QuotesCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class QuoteRepository wholesale
 */
class QuoteRepository implements QuoteRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $quotesFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $quotesCollectionFactory;

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
     * @param QuotesResource    $resource
     * @param QuotesFactory     $quotesFactory
     * @param QuotesCollection  $quotesCollectionFactory
     * @param DataObjectHelper  $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        QuotesResource $resource,
        QuotesFactory $quotesFactory,
        QuotesCollection $quotesCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->quotesFactory = $quotesFactory;
        $this->quotesCollectionFactory = $quotesCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Quote Complete data
     *
     * @param \Webkul\MpWholesale\Api\Data\QuoteInterface $quote
     * @return Quotes
     * @throws CouldNotSaveException
     */
    public function save(Data\QuoteInterface $quote)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $quote->setStoreId($storeId);
        try {
            $this->resource->save($quote);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $quote;
    }

    /**
     * Load Quote Complete data by given Block Identity
     *
     * @param string $id
     * @return Quotes
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $quote = $this->quotesFactory->create();
        $this->resource->load($quote, $id);
        if (!$quote->getEntityId()) {
            throw new NoSuchEntityException(__('Quote with id "%1" does not exist.', $id));
        }
        return $quote;
    }

    /**
     * Delete Quote
     *
     * @param \Webkul\MpWholesale\Api\Data\QuoteInterface $quote
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\QuoteInterface $quote)
    {
        try {
            $this->resource->delete($quote);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Quote by given Block Identity
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
