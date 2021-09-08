<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Block\Adminhtml\Ticketsmanagement\Tickets;

class Viewreply extends \Webkul\Helpdesk\Block\Adminhtml\Edit\Tab\AbstractAction
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepo;
    /**
     * @param \Webkul\Helpdesk\Model\TicketsPriorityFactory $ticketsPriorityFactory
     * @param \Webkul\Helpdesk\Model\TypeFactory $typeFactory
     * @param \Webkul\Helpdesk\Model\GroupFactory $groupFactory
     * @param \Webkul\Helpdesk\Model\AgentFactory $agentFactory
     * @param \Webkul\Helpdesk\Model\TicketsStatusFactory $ticketsStatusFactory
     * @param \Webkul\Helpdesk\Model\EmailTemplateFactory $emailTemplateFactory
     * @param \Webkul\Helpdesk\Model\TagFactory $tagFactory
     * @param \Webkul\Helpdesk\Model\ResponsesFactory $responsesFactory
     * @param \Webkul\Helpdesk\Model\EventsFactory $eventsFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config      $wysiwygConfig
     * @param \Webkul\Helpdesk\Model\SlapolicyFactory $slapolicyFactory
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\Helpdesk\Model\TicketsPriorityFactory $ticketsPriorityFactory,
        \Webkul\Helpdesk\Model\TypeFactory $typeFactory,
        \Webkul\Helpdesk\Model\GroupFactory $groupFactory,
        \Webkul\Helpdesk\Model\AgentFactory $agentFactory,
        \Webkul\Helpdesk\Model\TicketsStatusFactory $ticketsStatusFactory,
        \Webkul\Helpdesk\Model\EmailTemplateFactory $emailTemplateFactory,
        \Webkul\Helpdesk\Model\TagFactory $tagFactory,
        \Webkul\Helpdesk\Model\ResponsesFactory $responsesFactory,
        \Webkul\Helpdesk\Model\EventsFactory $eventsFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Webkul\Helpdesk\Model\SlapolicyFactory $slapolicyFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Webkul\Helpdesk\Model\TicketsFactory $ticketsFactory,
        \Webkul\Helpdesk\Model\TicketnotesFactory $ticketnotesFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute,
        \Webkul\Helpdesk\Model\ThreadFactory $threadFactory,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Webkul\Helpdesk\Helper\Data $dataHelper,
        \Webkul\Helpdesk\Helper\Tickets $ticketHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Filesystem\Io\File $filesystemFile,
        array $data = []
    ) {
        $this->_ticketsFactory = $ticketsFactory;
        $this->_ticketnotesFactory = $ticketnotesFactory;
        $this->_eavAttribute = $eavAttribute;
        $this->_threadFactory = $threadFactory;
        $this->assetRepo = $assetRepo;
        $this->dataHelper = $dataHelper;
        $this->ticketHelper = $ticketHelper;
        $this->filesystemFile = $filesystemFile;
        parent::__construct(
            $context,
            $ticketsPriorityFactory,
            $typeFactory,
            $groupFactory,
            $agentFactory,
            $ticketsStatusFactory,
            $emailTemplateFactory,
            $tagFactory,
            $responsesFactory,
            $eventsFactory,
            $wysiwygConfig,
            $slapolicyFactory,
            $userFactory,
            $dataHelper,
            $jsonHelper,
            $serializer,
            $data
        );
    }

    //get Current Ticket obejct
    public function getCurrentTicket()
    {
        $ticketId = $this->getRequest()->getParam('id');
        return $this->_ticketsFactory->create()->load($ticketId);
    }

    public function getNoteCollection($agentId, $ticketId)
    {
        return $this->_ticketnotesFactory->create()->getCollection()
                                  ->addFieldToFilter("agent_id", ["eq"=>$agentId])
                                  ->addFieldToFilter("ticket_id", ["eq"=>$ticketId]);
    }

    //get Attribute data by atrribute id
    public function getAttributeData($attributeId)
    {
        return $this->_eavAttribute->load($attributeId);
    }

    /**
     * getCollection Return threads according to config limit setting
     * @return Object $collection Threads Collection
     */
    public function getCollection($threadlimit, $ticketId)
    {
        $collection = $this->_threadFactory->create()->getCollection()
                            ->addFieldToFilter("ticket_id", $ticketId)
                            ->addFieldToFilter("thread_type", ["neq"=>"create"]);
        $collection->setOrder('entity_id', 'DESC');
        $collection->setPageSize($threadlimit);
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

    public function pdfFileUrl()
    {
        return $this->_assetRepo->getUrl("Webkul_Helpdesk::images/pdf.png");
    }

    public function docFileUrl()
    {
        return $this->_assetRepo->getUrl("Webkul_Helpdesk::images/doc.png");
    }

    public function helperData()
    {
        return $this->dataHelper;
    }

    public function ticketHelper()
    {
        return $this->ticketHelper;
    }

    public function fileExists($file)
    {
        return $this->filesystemFile->fileExists($file);
    }
}
