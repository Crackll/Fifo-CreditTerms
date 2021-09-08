<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Block\Adminhtml;

class Dashboard extends \Magento\Framework\View\Element\Template
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Webkul\Helpdesk\Model\AgentFactory $agentFactory,
        \Webkul\Helpdesk\Model\ActivityFactory $activityFactory,
        \Webkul\Helpdesk\Helper\Data $helper,
        \Webkul\Helpdesk\Helper\Tickets $ticketHelper
    ) {
        $this->_ticketsFactory = $ticketsFactory;
        $this->_authSession = $authSession;
        $this->_agentFactory = $agentFactory;
        $this->_activityFactory = $activityFactory;
        $this->_helper = $helper;
        $this->ticketHelper = $ticketHelper;
        parent::__construct($context);
    }

    /**
     * getTicketCountByStatus This function returns ticket count by status
     * @return Int count
     */
    public function getTicketCountByStatus($status)
    {
        $ticketColl = $this->_ticketsFactory->create()->getCollection()
                                            ->addFieldToFilter("status", ["eq"=>$status]);
        $agent = $this->_authSession->getUser();
        $agentModel = $this->_agentFactory->create()->getCollection()
                                            ->addFieldToFilter("user_id", ["eq"=>$agent->getId()])
                                            ->getFirstItem();

        if ($agentModel->getTicketScope() == 2) {
            $ticketColl->addFieldToFilter(
                ['to_group', 'to_agent'],
                [
                    ['eq'=>$agentModel->getGroupId()],
                    ['eq'=>$agent->getId()]
                ]
            );
        } elseif ($agentModel->getTicketScope() == 3) {
            $ticketColl->addFieldToFilter("to_agent", ["eq"=>$agent->getId()]);
        }
        return count($ticketColl);
    }

    /**
     * getDashboardActivity This function returns Activity
     * @return Int count
     */
    public function getDashboardActivity()
    {
        $agents = [];
        $collection = $this->_activityFactory->create()->getCollection();
        $agent = $this->_authSession->getUser();
        $agentModel = $this->_agentFactory->create()->getCollection()
                            ->addFieldToFilter("user_id", [$agent->getId()])
                            ->getFirstItem();
        if ($agentModel->getTicketScope() == 2) {
            $agentCollection = $this->_agentFactory->create()->getCollection()
                                    ->addFieldToFilter('group_id', [$agentModel->getGroupId()]);
            $temp = [];
            foreach ($agentCollection as $row) {
                array_push($agents, $row->getUserId());
            }
            $agents[] = $agent->getId();
            $collection->addFieldToFilter("user_id", ["in"=>$agents]);
            $collection->addFieldToFilter("user_type", ["eq"=>"Agent"]);
        } elseif ($agentModel->getTicketScope() == 3) {
            $collection->addFieldToFilter("user_id", ["eq"=>$agent->getId()]);
            $collection->addFieldToFilter("user_type", ["eq"=>"Agent"]);
        }
        $collection->setOrder("id", "desc");
        $dashboardactivitylimit = $this->_helper->getDashboardActivityLimit();
        $collection->setPageSize($dashboardactivitylimit);
        return $collection;
    }

    public function helperData()
    {
        return $this->_helper;
    }

    public function ticketHelper()
    {
        return $this->ticketHelper;
    }
}
