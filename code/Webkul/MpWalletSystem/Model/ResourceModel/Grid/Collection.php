<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Model\ResourceModel\Grid;

use Webkul\MpWalletSystem\Ui\Component\DataProvider\Document;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Webkul\MpWalletSystem\Logger\Logger as Logger;

/**
 * Webkul MpWalletSystem Model Class
 */
class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @inheritdoc
     */
    protected $document = Document::class;

    /**
     * @inheritdoc
     */
    protected $_map = ['fields' => ['entity_id' => 'main_table.entity_id']];

    /**
     * Initialize dependencies.
     * Phpcs:disable
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'wk_ws_wallet_transaction',
        $resourceModel = \Webkul\MpWalletSystem\Model\ResourceModel\Wallettransaction::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }

    /**
     * Render filters
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->join(
            ['customer_grid_flat' => $this->getTable('customer_grid_flat')],
            'customer_grid_flat.entity_id = main_table.customer_id',
            ['customerName' => 'name']
        );
        parent::_renderFiltersBefore();
    }
}
