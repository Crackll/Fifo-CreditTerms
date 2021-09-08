<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceProductLabels\Model\Label;

use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Webkul\MarketplaceProductLabels\Model\ResourceModel\Label\CollectionFactory;
use Webkul\MarketplaceProductLabels\Helper\Data;
 
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Array
     */
    protected $_loadedData;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var \Webkul\MarketplaceProductLabels\Helper\Data
     */
    protected $helper;

    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        FileSystem $fileSystem,
        CollectionFactory $collectionFactory,
        Data $helper,
        array $meta = [],
        array $data = []
    ) {
        $this->fileSystem = $fileSystem;
        $this->collection = $collectionFactory->create();
        $this->helper = $helper;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }
 
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $applications = $this->collection->getItems();
        $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
        $path = $mediaDirectory->getAbsolutePath("mplabel/label/");
        $mediaUrl = $this->helper->getMediaUrl()."mplabel/label/";
        foreach ($applications as $application) {
            $applicationData = $application->getData();
            $labels = [];
            $labels[0]['image'] = $applicationData['image_name'];
            $labels[0]['name'] = $applicationData['image_name'];
            $labels[0]['url'] = $mediaUrl.$applicationData['image_name'];
            $applicationData['labels'] = $labels;
            $this->_loadedData[$application->getId()] = $applicationData;
        }
        return $this->_loadedData;
    }
}
