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

class View extends Navigation
{
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
    protected $_eavAttribute;
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
        \Webkul\Helpdesk\Helper\Tickets $ticketsHelper,
        \Webkul\Helpdesk\Helper\Data $dataHelper,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Model\ThreadFactory $threadFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem\Io\File $filesystemFile,
        \Webkul\Helpdesk\Helper\Tickets $tickets,
        \Webkul\Helpdesk\Helper\Data $helpdeskTickets,
        \Magento\Framework\Json\Helper\Data $jsonData,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->dataHelper = $dataHelper;
        $this->_ticketsHelper = $ticketsHelper;
        $this->_ticketsFactory = $ticketsFactory;
        $this->_threadFactory = $threadFactory;
        $this->_eavAttribute = $eavAttribute;
        $this->assetRepo = $assetRepo;
        $this->jsonHelper = $jsonHelper;
        $this->filesystemFile = $filesystemFile;
        parent::__construct(
            $context,
            $supportcenterFactory,
            $cmspageFactory,
            $typeFactory,
            $ticketsPriorityFactory,
            $groupFactory,
            $ticketsStatusFactory,
            $ticketsCusAttrFactory,
            $eavAttribute,
            $tickets,
            $helpdeskTickets,
            $jsonData,
            $resource,
            $data
        );
    }

    //get Current Ticket obejct
    public function getCurrentTicket()
    {
        $ticketId = $this->getRequest()->getParam('id');
        return $this->_ticketsFactory->create()->load($ticketId);
    }

    /**
     * getCollection Return threads according to config limit setting
     * @return Object $collection Threads Collection
     */
    public function getCollection($maxThreadLimit, $ticketId)
    {
        $collection = $this->_threadFactory->create()->getCollection()
                            ->addFieldToFilter("ticket_id", $ticketId)
                            ->addFieldToFilter("thread_type", ["eq"=>"reply"]);
        $collection->setOrder('entity_id', 'DESC');
        $collection->setPageSize($maxThreadLimit);
        $collection->setCurPage($this->getCurrentPage());
        return $collection;
    }

    /**
     * getCurrentPage This function returns the current page count
     * @return Int current page count
     */
    public function getCurrentPage()
    {
        $count = $this->getRequest()->getParam("currnetpage");
        if ($count) {
            return $count;
        } else {
            return 1;
        }
    }

     //get Attribute data by atrribute id
    public function getAttributeData($attributeId)
    {
        return $this->_eavAttribute->load($attributeId);
    }

    public function pdfFileUrl()
    {
        return $this->_assetRepo->getUrl("Webkul_Helpdesk::images/pdf.png");
    }

    public function docFileUrl()
    {
        return $this->_assetRepo->getUrl("Webkul_Helpdesk::images/doc.png");
    }

    public function getTicketHelper()
    {
        return $this->_ticketsHelper;
    }

    public function getDataHelper()
    {
        return $this->dataHelper;
    }

    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    public function filexists($data)
    {
        return $this->filesystemFile->fileExists($data);
    }
}
