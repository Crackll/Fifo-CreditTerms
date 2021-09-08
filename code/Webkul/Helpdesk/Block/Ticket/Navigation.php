<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Helpdesk\Block\Ticket;

class Navigation extends \Magento\Framework\View\Element\Template
{
    const TABLE_NAME = "cms_page";
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $session;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param Customer                                         $customer
     * @param \Magento\Customer\Model\Session                  $session
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Helpdesk\Model\SupportCenterFactory $supportcenterFactory,
        \Magento\Cms\Model\PageFactory $cmspageFactory,
        \Webkul\Helpdesk\Model\TypeFactory $typeFactory,
        \Webkul\Helpdesk\Model\TicketsPriorityFactory $ticketsPriorityFactory,
        \Webkul\Helpdesk\Model\GroupFactory $groupFactory,
        \Webkul\Helpdesk\Model\TicketsStatusFactory $ticketsStatusFactory,
        \Webkul\Helpdesk\Model\TicketsCustomAttributesFactory $ticketsCusAttrFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute,
        \Webkul\Helpdesk\Helper\Tickets $tickets,
        \Webkul\Helpdesk\Helper\Data $helpdeskTickets,
        \Magento\Framework\Json\Helper\Data $jsonData,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->_supportcenterFactory = $supportcenterFactory;
        $this->_cmspageFactory = $cmspageFactory;
        $this->_typeFactory = $typeFactory;
        $this->_ticketsPriorityFactory = $ticketsPriorityFactory;
        $this->_groupFactory = $groupFactory;
        $this->_ticketsStatusFactory = $ticketsStatusFactory;
        $this->_ticketsCusAttrFactory = $ticketsCusAttrFactory;
        $this->_eavAttribute = $eavAttribute;
        $this->tickets = $tickets;
        $this->helpdeskTickets = $helpdeskTickets;
        $this->jsonData = $jsonData;
        $this->resource = $resource->getConnection();
        parent::__construct($context, $data);
    }

    //Return support center collection
    public function getSupportCenterData()
    {
        $params = $this->getRequest()->getParam('serach');
        $cmsTable = $this->resource->getTableName(self::TABLE_NAME);
        $supportCenter = $this->_supportcenterFactory->create()->getCollection()
                                    ->addFieldToFilter("status", ["eq"=>1]);
        if ($params) {
            $supportCenter->getSelect()
            ->join(
                ['cms'=>$cmsTable],
                'main_table.cms_id = cms.identifier',
                ['cms.*']
            )
            ->where('title = "'.$params.'"');
        }
        return $supportCenter;
    }

    //get CMS Page
    public function getCmsPages()
    {
        $cmsTable = $this->resource->getTableName(self::TABLE_NAME);
        $supportCenter = $this->_supportcenterFactory->create()->getCollection()
                                    ->addFieldToFilter("status", ["eq"=>1]);
        return $supportCenter;
    }

    //get CMS pages
    public function getCmsPage($cmsId, $column = 'identifier')
    {
        return $this->_cmspageFactory->create()->load($cmsId, $column);
    }

    //return ticket type array
    public function getTicketTypeArray()
    {
        return $this->_typeFactory->create()->toOptionArray();
    }

    //return ticket type array
    public function getTicketPriorityArray()
    {
        return $this->_ticketsPriorityFactory->create()->toOptionArray();
    }

    //get Ticket group Array
    public function getTicketGroupArray()
    {
        return $this->_groupFactory->create()->toOptionArray();
    }

    //Get Ticket Status Array
    public function getTicketStatusArray()
    {
        return $this->_ticketsStatusFactory->create()->toOptionArray();
    }

    //Get Custom Attribute Collection
    public function getTicketcustomAttributeCollection()
    {
        return $this->_ticketsCusAttrFactory->create()->getCollection()
                                ->addFieldToFilter("status", ["eq"=>1]);
    }

    //return Attribute Data by id
    public function getAttributeById($attributeId)
    {
        return $this->_eavAttribute->load($attributeId);
    }

    //return ticket helper
    public function ticketHelper()
    {
        return $this->tickets;
    }

    //return data helper
    public function dataHelper()
    {
        return $this->helpdeskTickets;
    }

    //return json Data
    public function jsonData()
    {
        return $this->jsonData;
    }
}
