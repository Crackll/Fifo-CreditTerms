<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Helpdesk\Ui\Component\Listing\Columns\Report;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

/**
 * Class Helpdesk Actions.
 */
class CustomerFirstResponse extends Column
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Helper\Data $helperData,
        \Webkul\Helpdesk\Model\ThreadFactory $threadFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_threadFactory = $threadFactory;
        $this->_helperData = $helperData;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $indexName = $this->getData('name');
                switch ($indexName) {
                    case 'first_response_time':
                        $collection = $this->_ticketsFactory->create()->getCollection()
                                        ->addFieldToFilter("customer_id", ["eq"=>$item['entity_id']]);
                        $firstResponseTime = 0;
                        $count = 0;
                        $count = $this->firstResponse($collection, $firstResponseTime, $count);
                        $fResponse = json_decode($count);
                        if (!$fResponse[1]) {
                            $fResponse[1] = 1;
                        }
                        $item['first_response_time'] = (round(abs($fResponse[0]) / $fResponse[1], 2))." ".__("Seconds");
                        break;
                }
            }
        }
        return $dataSource;
    }

    public function firstResponse($collection, $firstResponseTime, $count)
    {
        foreach ($collection as $key => $ticket) {
            $threadCollection = $this->_threadFactory->create()->getCollection()
                                    ->addFieldToFilter("ticket_id", ["eq"=>$ticket->getId()])
                                    ->addFieldToFilter(
                                        ['thread_type', 'thread_type'],
                                        [
                                                        ['eq'=>'create'],
                                                        ['eq'=>'reply']
                                                    ]
                                    );

            $customerTime =0;
            $agentTime =0;
            foreach ($threadCollection as $thread) {
                if ($thread->getWhoIs() == "admin") {
                    $agentTime = strtotime($thread->getCreatedAt());
                    break;
                }
            }
            foreach ($threadCollection as $thread) {
                if ($thread->getWhoIs() == "customer" &&
                $agentTime < strtotime($thread->getCreatedAt())) {
                    $customerTime = strtotime($thread->getCreatedAt());
                    break;
                }
            }

            $remainingSeconds = ($customerTime - $agentTime);
            if ($remainingSeconds < 0) {
                $firstResponseTime = $firstResponseTime + $remainingSeconds;
                $count++;
            }
        }
        
        return json_encode([$firstResponseTime,$count]);
    }
}
