<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRewardSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software protected Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRewardSystem\Observer;

use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;

class CartProductAddAfterObserver implements ObserverInterface
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @param \Magento\Framework\Event\Manager $eventManager
     * @param SessionManager $session
     */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        SessionManager $session
    ) {
        $this->session = $session;
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->session->unsRewardInfo();
    }
}
