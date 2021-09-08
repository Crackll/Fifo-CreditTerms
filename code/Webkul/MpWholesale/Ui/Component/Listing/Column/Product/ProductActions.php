<?php
/**
 * @category  Webkul
 * @package   Webkul_MpWholesale
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Ui\Component\Listing\Column\Product;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class ProductActions extends Column
{
    /** Url path */
    const WHOLESALE_PRODUCT_EDIT = 'mpwholesale/product/edit';
    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * @var string
     */
    private $editUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param [type] $editUrl
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Magento\Backend\Model\Auth\Session $authSession,
        $editUrl = self::WHOLESALE_PRODUCT_EDIT,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->editUrl = $editUrl;
        $this->authSession = $authSession;
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
                $name = $this->getData('name');
                $role = $this->authSession->getUser()->getRole();
                if ($role->getRoleName() == 'Wholesaler') {
                    if (isset($item['entity_id'])) {
                        $item[$name]['edit'] = [
                            'href' => $this->urlBuilder->getUrl(
                                $this->editUrl,
                                ['id' => $item['entity_id']]
                            ),
                            'label' => __('Edit')
                        ];
                    }
                } else {
                    if (isset($item['entity_id'])) {
                        if (isset($item['approve_status']) && $item['approve_status'] == 0) {
                            $item[$name]['edit'] = [
                                'href' => $this->urlBuilder->getUrl(
                                    'mpwholesale/product/approve',
                                    ['id' => $item['entity_id']]
                                ),
                                'label' => __('Approve')
                            ];
                        } else {
                            $item[$name]['edit'] = [
                                'href' => $this->urlBuilder->getUrl(
                                    'mpwholesale/product/reject',
                                    ['id' => $item['entity_id']]
                                ),
                                'label' => __('Reject')
                            ];
                        }
                    }
                }
            }
        }

        return $dataSource;
    }
}
