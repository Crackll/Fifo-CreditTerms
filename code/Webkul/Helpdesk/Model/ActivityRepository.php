<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Model;

use \Magento\Framework\Exception\CouldNotSaveException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ActivityRepository implements \Webkul\Helpdesk\Api\ActivityRepositoryInterface
{
    /**
     * @var \Webkul\Helpdesk\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Webkul\Helpdesk\Model\CustomerFactory
     */
    protected $_helpdeskCustomerFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

    /**
     * TicketsRepository constructor.
     * @param \Webkul\Helpdesk\Helper\Data $helper
     * @param \Webkul\Helpdesk\Model\CustomerFactory $helpdeskCustomerFactory
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Webkul\Helpdesk\Helper\Data $helper,
        \Webkul\Helpdesk\Model\CustomerFactory $helpdeskCustomerFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\ActivityFactory $activityFactory,
        \Webkul\Helpdesk\Helper\Tickets $ticketsHelper,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory
    ) {
        $this->_helper = $helper;
        $this->_helpdeskCustomerFactory = $helpdeskCustomerFactory;
        $this->_authSession = $authSession;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_activityFactory = $activityFactory;
        $this->_ticketsHelper = $ticketsHelper;
        $this->_ticketsFactory = $ticketsFactory;
    }

    /**
     * saveActivity Save Helpdesk activity
     * @param int $id Entity Id
     * @param String $name Entity Name
     * @param String $type Activity Type
     * @param int $field Activity Field
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function saveActivity($id, $name, $type, $field)
    {
        try {
            $allowedActivity = $this->_helper->getAllowedActivity();
            $allowedActivity = explode(',', $allowedActivity);
            if (!in_array($field, $allowedActivity)) {
                return false;
            }

            if ($this->_helper->isAdmin()) {
                $userType = "Agent";
                $agent = $this->_authSession->getUser();
                $performerName = $agent->getName();
                $performerId = $agent->getId();
            } else {
                $userType = "Customer";
                $ticket = $this->_ticketsFactory->create()->load($id);
                $performerName = $ticket->getFullname();
                $performerId = $ticket->getCustomerId();
            }
            $addPriority = $this->_helper->getActivityPriorityAdd();
            $editPriority = $this->_helper->getActivityPriorityEdit();
            $deletePriority = $this->_helper->getActivityPriorityDelete();

            $activity = $this->_activityFactory->create();
            $optionLabels = $activity->fieldLabelOptions();
            if ($field == "ticket") {
                if ($type == "add") {
                    $msg = $userType." - ".$performerName.",added ".$optionLabels[$field]." ".$name." , id - #".$id;
                    $activity->setLevel($addPriority);
                } elseif ($type == "edit") {
                    $msg = $userType." - ".$performerName.",edited ".$optionLabels[$field]." ".$name.", id - #".$id;
                    $activity->setLevel($editPriority);
                } elseif ($type == "delete") {
                    $msg = $userType." - ".$performerName.",deleted ".$optionLabels[$field]." ".$name.", id - #".$id;
                    $activity->setLevel($deletePriority);
                }
            } else {
                if ($type == "add") {
                    $msg = $userType." - ".$performerName.",added ".$optionLabels[$field]." 
                    with name ".$name.", id - ".$id;
                    $activity->setLevel($addPriority);
                } elseif ($type == "edit") {
                    $msg = $userType." - ".$performerName.",edited ".$optionLabels[$field]." 
                    with name ".$name.", id - ".$id;
                    $activity->setLevel($editPriority);
                } elseif ($type == "delete") {
                    $msg = $userType." - ".$performerName.",deleted ".$optionLabels[$field]." 
                    with name ".$name.", id - ".$id;
                    $activity->setLevel($deletePriority);
                }
            }
            $activity->setUserId($performerId);
            $activity->setUserType($userType);
            $activity->setPerformer($performerName);
            $activity->setType($type);
            $activity->setField($optionLabels[$field]);
            $activity->setLabel($msg);
            $activity->save();
        } catch (\Exception $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            throw new CouldNotSaveException(__('Unable to save activity'), $e);
        }
    }
}
