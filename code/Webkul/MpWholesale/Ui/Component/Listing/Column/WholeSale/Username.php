<?php
/**
 *
 * @category    Webkul
 * @package     Webkul_MpWholesale
 * @author      Webkul Software Private Limited
 *
 */
namespace Webkul\MpWholesale\Ui\Component\Listing\Column\WholeSale;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\User\Model\UserFactory;

/**
 * Class WholeSale Username
 */
class Username extends Column
{

    protected $userFactory;
    /**
     * Constructor
     *
     * @param ContextInterface    $context
     * @param UserFactory         $userFactory
     * @param UiComponentFactory  $uiComponentFactory
     * @param array               $components
     * @param array               $data
     */
    public function __construct(
        ContextInterface $context,
        UserFactory $userFactory,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->userFactory = $userFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        $userId = 0;
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (!empty($item['user_id'])) {
                    $userId = $item['user_id'];
                }
                $userName = $this->userFactory->create()
                            ->load($userId)->getUsername();
                $item[$this->getData('name')] = $userName;
            }
        }
        return $dataSource;
    }
}
