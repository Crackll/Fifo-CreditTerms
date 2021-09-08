<?php

namespace Webkul\MpAdvertisementManager\Plugin;

/**
 * Class afterUpdateItems
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateCart
{

    protected $_messageManager;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_messageManager = $messageManager;
    }

    public function afterUpdateItems($subject, $result, $data)
    {

        foreach ($data as $itemId => $itemInfo) {
            $item = $subject->getQuote()->getItemById($itemId);
            if ($item->getName()=="Ads Plan") {
                $item->setQty(1);
                $this->_messageManager->addWarning(__("This action cannot be performed."));
            }
        }
        return $result;
    }
}
