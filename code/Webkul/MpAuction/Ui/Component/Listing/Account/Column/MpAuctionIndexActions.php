<?php
/**
 * Webkul MpAuction Action UI.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAuction\Ui\Component\Listing\Account\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class MpAuctionIndexActions extends Column
{
    /** Url path */
    const MPAUCTION_ADD_URL = 'mpauction/account/addauction';
    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $addUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @param string $addUrl
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $addUrl = self::MPAUCTION_ADD_URL
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->addUrl = $addUrl;
        parent::__construct($context, $uiComponentFactory, $components, $data);
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
            foreach ($dataSource['data']['items'] as &$item) {
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        $this->addUrl,
                        ['pid' => $item['entity_id']]
                    ),
                    'label' => __('Add Auction'),
                    'hidden' => false,
                ];
            }
        }
        return $dataSource;
    }
}
