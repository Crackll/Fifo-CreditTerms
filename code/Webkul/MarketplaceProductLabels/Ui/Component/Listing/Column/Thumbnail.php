<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceProductLabels
 * @author    Webkul Software Private Limited
 * @copyright Copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplaceProductLabels\Ui\Component\Listing\Column;
 
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Webkul\MarketplaceProductLabels\Model\LabelFactory;
 
class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var string
     */
    const ALT_FIELD = 'name';
 
    /**
     * @var LabelFactory
     */
    protected $labelFactory;

    /**
     * @var \Webkul\MarketplaceProductLabels\Model\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;
 
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Webkul\MarketplaceProductLabels\Model\Image\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Webkul\MarketplaceProductLabels\Model\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        LabelFactory $labelFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->labelFactory = $labelFactory;
    }
 
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $label = $this->loadLabel($item['id']);
                $item[$fieldName.'_src'] = $this->imageHelper->getBaseUrl().'mplabel/label/'.$label['image_name'];
                $item[$fieldName.'_orig_src'] = $this->imageHelper->getBaseUrl().'mplabel/label/'.$label['image_name'];
            }
        }
 
        return $dataSource;
    }
   
    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;

        return isset($row[$altField]) ? $row[$altField] : null;
    }

    /**
     * Load Label
     *
     * @param [type] $id
     * @return label
     */
    public function loadLabel($id)
    {
        $label = $this->labelFactory->create()->load($id);
        return $label;
    }
}
