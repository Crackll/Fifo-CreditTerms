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
class Agent extends Column
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
                    case 'total_ticket':
                        $collection = $this->_ticketsFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter("to_agent", ["eq"=>$item['user_id']]);
                        $item[$this->getData('name')] = count($collection);
                        break;

                    case 'resolve_ticket':
                        $resolveStatus = $this->_helperData->getConfigResolveStatus();
                        $collection = $this->_ticketsFactory->create()
                                              ->getCollection()
                                              ->addFieldToFilter("to_agent", ["eq"=>$item['user_id']])
                                              ->addFieldToFilter("status", ["eq"=>$resolveStatus]);
                        $item[$this->getData('name')] = count($collection);
                        break;

                    case 'first_response_time':
                        $collection = $this->_ticketsFactory->create()->getCollection()
                                        ->addFieldToFilter("to_agent", ["eq"=>$item['user_id']]);
                        $firstResponseTime = 0;
                        $count = 0;
                        $this->firstResponseTime($collection, $firstResponseTime, $count);
                        
                        if (!$count) {
                            $count = 1;
                        }
                        $item[$this->getData('name')] = round($firstResponseTime / $count, 2)." ".__("Seconds");
                        break;

                    case 'avg_response_time':
                        $resolveStatus = $this->_helperData->getConfigResolveStatus();
                        $closeStatus = $this->_helperData->getConfigCloseStatus();
                        $collection = $this->_ticketsFactory->create()->getCollection()
                                        ->addFieldToFilter("to_agent", ["eq"=>$item['user_id']])
                                        ->addFieldToFilter(
                                            ['status', 'status'],
                                            [
                                                                ['eq'=>$resolveStatus],
                                                                ['eq'=>$closeStatus]
                                                            ]
                                        );
                        $resolveTime = 0;
                        $count = 0;
                        $this->avgResponseTime($collection, $resolveTime, $count);
                        if (!$count) {
                            $count = 1;
                        }
                        $item[$this->getData('name')] = round($resolveTime / $count, 2)." ".__("Seconds");
                        break;
                }
            }
        }

        return $dataSource;
    }

    public function firstResponseTime($collection, $firstResponseTime, $count)
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

            $customerTime = 0;
            $agentTime = 0;
            foreach ($threadCollection as $thread) {
                if ($thread->getWhoIs() == "customer") {
                    $customerTime = strtotime($thread->getCreatedAt());
                    break;
                }
            }
            foreach ($threadCollection as $thread) {
                if (($thread->getWhoIs() == "admin" ||
                $thread->getWhoIs() == "agent") &&
                $customerTime < strtotime($thread->getCreatedAt())) {
                    $agentTime = strtotime($thread->getCreatedAt());
                    break;
                }
            }
            $remainingSeconds = ($agentTime - $customerTime);
            if ($remainingSeconds > 0) {
                $firstResponseTime = $firstResponseTime + $remainingSeconds;
                $count++;
            }
        }
        return $count;
    }

    public function avgResponseTime($collection, $resolveTime, $count)
    {
        foreach ($collection as $key => $ticket) {
            $firstThread = $this->_threadFactory->create()->getCollection()
                                    ->addFieldToFilter("ticket_id", ["eq"=>$ticket->getId()])
                                    ->addFieldToFilter(
                                        ['thread_type', 'thread_type'],
                                        [
                                                        ['eq'=>'create'],
                                                        ['eq'=>'reply']
                                                    ]
                                    )
                                    ->getFirstItem();

            $customerTime = "";
            $lastTime = "";
            if ($firstThread->getId()) {
                $fisrtTime = strtotime($firstThread->getCreatedAt());
            }

            $lastThread = $this->_threadFactory->create()->getCollection()
                                    ->addFieldToFilter("ticket_id", $ticket->getId())
                                    ->addFieldToFilter(
                                        ['thread_type', 'thread_type'],
                                        [
                                            ['eq'=>'create'],
                                            ['eq'=>'reply']
                                        ]
                                    )
                                    ->getLastItem();
            if ($lastThread->getId()) {
                $lastTime = strtotime($lastThread->getCreatedAt());
            }
            $remainingSeconds = ($lastTime - $fisrtTime);
            if ($remainingSeconds > 0 && $lastTime != "" && $fisrtTime != "") {
                $resolveTime = $resolveTime + $remainingSeconds;
                $count++;
            }
        }
        return $count;
    }
}
