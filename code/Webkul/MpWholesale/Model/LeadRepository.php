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
use Webkul\MpWholesale\Api\LeadRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpWholesale\Model\ResourceModel\Leads as LeadsResource;
use Webkul\MpWholesale\Model\ResourceModel\Leads\CollectionFactory as LeadsCollection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class LeadRepository WholeSale
 */
class LeadRepository implements LeadRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $leadsFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $leadsCollectionFactory;

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
     * @param LeadsResource    $resource
     * @param LeadsFactory     $leadsFactory
     * @param LeadsCollection  $leadsCollectionFactory
     * @param DataObjectHelper  $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        LeadsResource $resource,
        LeadsFactory $leadsFactory,
        LeadsCollection $leadsCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->leadsFactory = $leadsFactory;
        $this->leadsCollectionFactory = $leadsCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Lead Complete data
     *
     * @param \Webkul\MpWholesale\Api\Data\LeadInterface $lead
     * @return Leads
     * @throws CouldNotSaveException
     */
    public function save(Data\LeadInterface $lead)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $lead->setStoreId($storeId);
        try {
            $this->resource->save($lead);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $lead;
    }

    /**
     * Load Lead Complete data by given Block Identity
     *
     * @param string $id
     * @return Leads
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $lead = $this->leadsFactory->create();
        $this->resource->load($lead, $id);
        if (!$lead->getEntityId()) {
            throw new NoSuchEntityException(__('Lead with id "%1" does not exist.', $id));
        }
        return $lead;
    }

    /**
     * Delete Lead
     *
     * @param \Webkul\MpWholesale\Api\Data\LeadInterface $lead
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\LeadInterface $lead)
    {
        try {
            $this->resource->delete($lead);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Lead by given Block Identity
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
