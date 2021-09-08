<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Controller\Adminhtml\Tag;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Savetag extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Helpdesk\Model\TagFactory $tagFactory,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->_tagFactory = $tagFactory;
        $this->_helpdeskLogger = $helpdeskLogger;
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $wholedata = $this->getRequest()->getParams();
        $tag = $this->_tagFactory->create();
        $tag->setName($wholedata['value']);
        $tag->setTicketIds($wholedata['ticket_id']);
        $id = $tag->save()->getId();

        $tagNames = [];
        $tagCollection = $this->_tagFactory->create()->getCollection();
        foreach ($tagCollection as $tag) {
            $ticketIds = explode(",", $tag->getTicketIds());
            if (in_array($wholedata['ticket_id'], $ticketIds)) {
                array_push($tagNames, $tag->getName());
            }
        }
        $data = ["names"=>implode(',', $tagNames),"id"=>$id,"name"=>$wholedata['value']];
        $this->getResponse()->setHeader('Content-type', 'text/html');
        $this->getResponse()->setBody(json_encode($data));
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::tag');
    }
}
