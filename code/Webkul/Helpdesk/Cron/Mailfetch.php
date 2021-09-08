<?php
/**
 * Webkul Helpdesk Config
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Helpdesk\Cron;

/**
 * custom cron actions
 */
class Mailfetch
{
    /**
     * Mailfetch constructor.
     *
     * @param \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger
     * @param \Webkul\Helpdesk\Model\MailfetchRepository $mailfetchRepo
     */
    public function __construct(
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Webkul\Helpdesk\Model\MailfetchRepository $mailfetchRepo,
        \Webkul\Helpdesk\Model\ConnectEmailFactory $connectEmailFactory
    ) {
        $this->helpdeskLogger = $helpdeskLogger;
        $this->mailfetchRepo = $mailfetchRepo;
        $this->_connectEmailFactory = $connectEmailFactory;
    }

    public function execute()
    {
        try {
            $this->helpdeskLogger->info("Run Mailfetch Cron Method");
            $connectEmailCollection = $this->_connectEmailFactory->create()
                                ->getCollection()
                                ->addFieldToFilter("status", ["eq"=>1]);
            foreach ($connectEmailCollection as $connectEmail) {
                $this->mailfetchRepo->fetchMail($connectEmail->getId());
            }
        } catch (\Exception $e) {
            $this->helpdeskLogger->info("cron job error :".$e->getMessage());
        }
    }
}
