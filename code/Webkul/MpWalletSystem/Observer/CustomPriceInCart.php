<?php
namespace Webkul\MpWalletSystem\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use \Magento\Checkout\Model\Session as CheckoutSession;
class CustomPriceInCart implements ObserverInterface
{
    public function __construct(CheckoutSession $checkoutSession) {
        $this->checkoutSession = $checkoutSession;
    }

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('------Your text message---');
    
        $quote = $this->checkoutSession->getQuote();
        $quoteItems= $quote->getAllItems();
        foreach ($quoteItems as $item )
        {
             
            $price = 100; //set your price here
            $item->setCustomPrice($price);
            $item->setOriginalCustomPrice($price);
            $item->getProduct()->setIsSuperMode(true);
            $item->save();
            $logger->info($item->getCustomPrice());
        }
        $quote->save();
		
	}
}