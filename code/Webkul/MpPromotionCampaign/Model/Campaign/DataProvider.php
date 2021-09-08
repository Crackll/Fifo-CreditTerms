<?php
/**
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPromotionCampaign\Model\Campaign;

use Webkul\MpPromotionCampaign\Model\ResourceModel\Campaign\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{

    protected $loadedData;

    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Webkul\MpPromotionCampaign\Helper\Data
     */
    protected $_helper;

    /**
     * Constructor
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param \Webkul\MpPromotionCampaign\Helper\Data $helper
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        \Webkul\MpPromotionCampaign\Helper\Data $helper,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->_helper = $helper;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $result['general'] = $model->getData();
            $bannerImage = $model->getBanner();
            if (!empty($bannerImage)) {
                $banner = [];
                $banner[0]['name'] = $bannerImage;
                $banner[0]['url'] = $this->_helper->getMediaUrl($bannerImage);
                $result['promotion']['banner'] = $banner;
            }
            $result['terms']['description'] = $model->getDescription();
            $result['entity_id'] = $model->getId();
            $this->loadedData[$model->getId()] = $result;
        }
        $data = $this->dataPersistor->get('campaign');

        if (!empty($data)) {
            $id = $data['campaign']['entity_id'] ?? null;
            $this->loadedData[$id] = $data;
            $this->dataPersistor->clear('campaign');
        }
        return $this->loadedData;
    }
}
