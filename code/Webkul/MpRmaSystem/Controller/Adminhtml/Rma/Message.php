<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Controller\Adminhtml\Rma;

class Message extends \Webkul\MpRmaSystem\Controller\Adminhtml\Rma
{
    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpRmaHelper;

    /**
     * @var \Webkul\MpRmaSystem\Model\ConversationFactory
     */
    protected $conversation;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param \Webkul\MpRmaSystem\Model\ConversationFactory $conversation
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        \Webkul\MpRmaSystem\Model\ConversationFactory $conversation
    ) {
        $this->mpRmaHelper = $mpRmaHelper;
        $this->conversation = $conversation;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $data['sender_type'] = 0;
        $data['created_time'] = date("Y-m-d H:i:s");
        $rmaId = $data['rma_id'];
        $IsCustomer = $this->mpRmaHelper->getCustmerByRmaId($rmaId);

        if (!$IsCustomer) {
            $this->messageManager->addError(__("Customer not exists"));
            return $this->resultRedirectFactory
                    ->create()
                    ->setPath(
                        '*/rma/edit',
                        ['id' => $rmaId, 'back' => null, '_current' => true]
                    );
        }
        $model = $this->conversation->create();
        $model->setData($data)->save();
        $this->messageManager->addSuccess(__("Message sent"));
        $this->mpRmaHelper->sendNewMessageEmail($data);
        return $this->resultRedirectFactory
                ->create()
                ->setPath(
                    '*/rma/edit',
                    ['id' => $rmaId, 'back' => null, '_current' => true]
                );
    }
}
