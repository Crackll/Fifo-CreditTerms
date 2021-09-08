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

class Addtag extends \Magento\Backend\App\Action
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
        $tag = $this->_tagFactory->create()->load($wholedata['id']);
        $ticketIds = explode(",", $tag->getTicketIds());
        if ($wholedata['flag'] == 0) {
            if (($key = array_search($wholedata['ticket_id'], $ticketIds)) !== false) {
                unset($ticketIds[$key]);
            }
        } else {
            array_push($ticketIds, $wholedata['ticket_id']);
        }
        $ticketIds = array_unique($ticketIds);
        $ticketIds = array_filter($ticketIds);
        $tag->setTicketIds(implode(',', $ticketIds));
        $tag->save();

        $tagNames = [];
        $tagCollection = $this->_tagFactory->create()->getCollection();
        foreach ($tagCollection as $tag) {
            $ticketIds = explode(",", $tag->getTicketIds());
            if (in_array($wholedata['ticket_id'], $ticketIds)) {
                array_push($tagNames, $tag->getName());
            }
        }
        $this->getResponse()->setHeader('Content-type', 'text/html');
        $this->getResponse()->setBody(implode(',', $tagNames));
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Helpdesk::tag');
    }
}
