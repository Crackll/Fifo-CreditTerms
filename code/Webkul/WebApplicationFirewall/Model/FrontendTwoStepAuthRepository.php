<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\WebApplicationFirewall\Model;

use Webkul\WebApplicationFirewall\Api\FrontendTwoStepAuthRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Webkul\WebApplicationFirewall\Model\ResourceModel\FrontendTwoStepAuth as ResourceFrontendTwoStepAuth;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthSearchResultsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Webkul\WebApplicationFirewall\Model\ResourceModel\FrontendTwoStepAuth\CollectionFactory
    as FrontendTwoStepAuthCollectionFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterfaceFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Exception\NoSuchEntityException;

class FrontendTwoStepAuthRepository implements FrontendTwoStepAuthRepositoryInterface
{
    /**
     * @var $dataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var $storeManager
     */
    private $storeManager;

    /**
     * @var $frontendTwoStepAuthFactory
     */
    protected $frontendTwoStepAuthFactory;

    /**
     * @var $frontendTwoStepAuthCollectionFactory
     */
    protected $frontendTwoStepAuthCollectionFactory;

    /**
     * @var $searchResultsFactory
     */
    protected $searchResultsFactory;

    /**
     * @var $dataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var $extensionAttributesJoinProcessor
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var $collectionProcessor
     */
    private $collectionProcessor;

    /**
     * @var $resource
     */
    protected $resource;

    /**
     * @var $extensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var $dataFrontendTwoStepAuthFactory
     */
    protected $dataFrontendTwoStepAuthFactory;

    /**
     * @param ResourceFrontendTwoStepAuth $resource
     * @param FrontendTwoStepAuthFactory $frontendTwoStepAuthFactory
     * @param FrontendTwoStepAuthInterfaceFactory $dataFrontendTwoStepAuthFactory
     * @param FrontendTwoStepAuthCollectionFactory $frontendTwoStepAuthCollectionFactory
     * @param FrontendTwoStepAuthSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceFrontendTwoStepAuth $resource,
        FrontendTwoStepAuthFactory $frontendTwoStepAuthFactory,
        FrontendTwoStepAuthInterfaceFactory $dataFrontendTwoStepAuthFactory,
        FrontendTwoStepAuthCollectionFactory $frontendTwoStepAuthCollectionFactory,
        FrontendTwoStepAuthSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->frontendTwoStepAuthFactory = $frontendTwoStepAuthFactory;
        $this->frontendTwoStepAuthCollectionFactory = $frontendTwoStepAuthCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataFrontendTwoStepAuthFactory = $dataFrontendTwoStepAuthFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface $frontendTwoStepAuth
    ) {
        $frontendTwoStepAuthData = $this->extensibleDataObjectConverter->toNestedArray(
            $frontendTwoStepAuth,
            [],
            \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface::class
        );

        $frontendTwoStepAuthModel = $this->frontendTwoStepAuthFactory->create()->setData($frontendTwoStepAuthData);

        try {
            $this->resource->save($frontendTwoStepAuthModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the frontendTwoStepAuth: %1',
                $exception->getMessage()
            ));
        }
        return $frontendTwoStepAuthModel;
    }

    /**
     * {@inheritdoc}
     */
    public function get($frontendTwoStepAuthId)
    {
        $frontendTwoStepAuth = $this->frontendTwoStepAuthFactory->create();
        $this->resource->load($frontendTwoStepAuth, $frontendTwoStepAuthId);
        if (!$frontendTwoStepAuth->getId()) {
            throw new NoSuchEntityException(
                __('FrontendTwoStepAuth with id "%1" does not exist.', $frontendTwoStepAuthId)
            );
        }
        return $frontendTwoStepAuth;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->frontendTwoStepAuthCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Webkul\WebApplicationFirewall\Api\Data\FrontendTwoStepAuthInterface $frontendTwoStepAuth
    ) {
        try {
            $frontendTwoStepAuthModel = $this->frontendTwoStepAuthFactory->create();
            $this->resource->load($frontendTwoStepAuthModel, $frontendTwoStepAuth->getFrontendtwostepauthId());
            $this->resource->delete($frontendTwoStepAuthModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the FrontendTwoStepAuth: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($frontendTwoStepAuthId)
    {
        return $this->delete($this->get($frontendTwoStepAuthId));
    }
}
