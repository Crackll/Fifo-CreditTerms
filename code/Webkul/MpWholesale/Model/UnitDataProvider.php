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

use Webkul\MpWholesale\Model\ResourceModel\WholeSalerUnit\CollectionFactory;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;

class UnitDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

    /**
     * Constructor.
     *
     * @param string                   $name
     * @param string                   $primaryFieldName
     * @param string                   $requestFieldName
     * @param CollectionFactory        $CollectionFactory
     * @param array                    $meta
     * @param array                    $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->collection->addFieldToSelect('*');
    }

    /**
     * Get session object.
     *
     * @return SessionManagerInterface
     */
    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = ObjectManager::getInstance()->get(
                \Magento\Framework\Session\SessionManagerInterface::class
            );
        }

        return $this->session;
    }

    /**
     * Get data
     * ads_pricing
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $result['unit_form'] = $item->getData();
            $this->loadedData[$item->getId()] = $result;
        }

        $data = $this->getSession()->getUnitFormData();
        if (!empty($data)) {
            $unitId = isset($data['unit_form']['entity_id'])
            ? $data['unit_form']['entity_id'] : null;
            $this->loadedData[$unitId] = $data;
            $this->getSession()->unsUnitFormData();
        }
        return $this->loadedData;
    }
}
