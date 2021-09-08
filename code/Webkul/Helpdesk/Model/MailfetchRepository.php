<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Model;

use \Magento\Framework\Exception\CouldNotSaveException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class MailfetchRepository implements \Webkul\Helpdesk\Api\MailfetchRepositoryInterface
{
    /**
     * @var \Webkul\Helpdesk\Helper\Data
     */
    protected $_connectemailFactory;

    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;
    const TABLENAME = "helpdesk_ticket_mail_details";

    /**
     * TicketsRepository constructor.
     * @param \Webkul\Helpdesk\Model\ConnectEmailFactory $connectemailFactory
     * @param \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Webkul\Helpdesk\Model\ConnectEmailFactory $connectemailFactory,
        \Webkul\Helpdesk\Model\MailfetchFactory $mailfetchFactory,
        \Webkul\Helpdesk\Model\ThreadRepository $threadRepo,
        \Webkul\Helpdesk\Model\TicketsRepository $ticketsRepo,
        \Webkul\Helpdesk\Model\EventsRepository $eventsRepo,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_connectemailFactory = $connectemailFactory;
        $this->_mailfetchFactory = $mailfetchFactory;
        $this->_threadRepo = $threadRepo;
        $this->_ticketsRepo = $ticketsRepo;
        $this->_eventsRepo = $eventsRepo;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
    }

    /**
     * fetchMail Fetch the email and create tickets and threads
     * @param Int $connectEmailId connect Email Id
     * @return Int Fetch email count
     */
    public function fetchMail($connectEmailId)
    {
        try {
            $connectEmail = $this->_connectemailFactory->create()->load($connectEmailId);
            $isHtml = true;
            $host = $connectEmail->getHostName();
            $port = $connectEmail->getPort();
            $userName = $connectEmail->getUsername();
            $password = $connectEmail->getPassword();
            $server = new \Fetch\Server($host, $port);
            $server->setAuthentication($userName, $password);
            $server->setFlag($connectEmail->getProtocol());
            $server->setMailBox($connectEmail->getMailboxFolder());
            $count = 0;
            if ($connectEmail->getCount() != $connectEmail->getFetchEmailLimit()) {
                $count = $connectEmail->getCount();
            }
            $limit = $count;
            $messages = array_reverse($server->getMessages($limit));
            if ($connectEmail->getCount() != $connectEmail->getFetchEmailLimit()) {
                $messages = array_chunk($messages, $connectEmail->getFetchEmailLimit());
                $messages = $messages[0];
            }
            $count = 0;
            foreach ($messages as $message) {
                $flag = $this->processMail($message, $connectEmailId);
                if ($flag) {
                    $count++;
                    if ($connectEmail->getHelpdeskAction() == 1) {
                        $message->delete();
                    } elseif ($connectEmail->getHelpdeskAction() == 2) {
                        if (!$server->hasMailBox($connectEmail->getMailboxFolder())) {
                            $server->createMailBox($connectEmail->getMailboxFolder());
                        }
                        $server->createMailBox($connectEmail->getMailboxFolder());
                        $message->moveToMailBox($connectEmail->getMailboxFolder());
                    }
                }
            }
            $connectEmail->setCount($connectEmail->getCount()+$connectEmail->getFetchEmailLimit());
            $connectEmail->save();
            return $count;
        } catch (\RuntimeException $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        } catch (\InvalidArgumentException $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        } catch (\Exception $e) {
            $this->_helpdeskLogger->info($e->getMessage());
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        }
    }

    /**
     * processMail Process the mail the create ticket from them
     * @param Object $message Mail message object
     * @param Int $connectEmailId connect Email Id
     * @return Int Fetch email count
     */
    public function processMail($message, $connectEmailId)
    {
        $connectEmail = $this->_connectemailFactory->create()->load($connectEmailId);
        $isHtml = true;
        $data = [];
        $header = $message->getHeaders();
        $count = 0;
        if (!isset($header->references) || $header->references == "") {
            $mailFetchCollection = $this->_mailfetchFactory->create()->getCollection()
                                        ->addFieldToFilter("u_id", ["eq"=>$message->getUid()]);
            if (!count($mailFetchCollection)) {
                $toAddress = $message->getAddresses("to");
                $to = [];
                foreach ($toAddress as $value) {
                    $to[] = $value['address'];
                }
                $address = $message->getAddresses("sender");
                $data['connect_email_id'] = $connectEmailId;
                $data['source'] = "email";
                $data['who_is'] = "customer";
                $data['email'] = $address['address'];
                $data['from'] = $address['address'];
                $data['to'] = implode(',', $to);
                if (isset($header->ccaddress)) {
                    $data['cc'] = $header->ccaddress;
                }
                if (isset($header->bccaddress)) {
                    $data['bcc'] = $header->bccaddress;
                }
                $data['fullname'] = isset($address['name'])?$address['name']:"";
                if ($data['fullname'] == "") {
                    $data['fullname'] = $data['email'];
                }
                $data['subject'] = $message->getSubject()??"No Subject";
                $data['query'] = $message->getMessageBody($isHtml);
                $ticketId = $this->_ticketsRepo->createTicket($data);
                $data['thread_type'] = "create";
                $threadId = $this->_threadRepo->createThread($ticketId, $data);
                $this->_eventsRepo->checkTicketEvent("ticket", $ticketId, "created");
                $mailFetchData[$header->message_id]['thread_id'] = $threadId;
                $mailFetchData[$header->message_id]['message_id'] = $header->message_id;
                $mailFetchData[$header->message_id]['sender'] = $address['address'];
                $mailFetchData[$header->message_id]['u_id'] = $message->getUid();
                $this->saveMailMessageData($mailFetchData);
                $count++;
            }
        } else {
            $mailFetchCollection = $this->_mailfetchFactory->create()->getCollection()
                                        ->addFieldToFilter("u_id", ["eq"=>$message->getUid()]);
            if (!count($mailFetchCollection)) {
                $reference = explode(' ', $header->references);
                $mailFetchItem = $this->_mailfetchFactory->create()->getCollection()
                                        ->addFieldToFilter("message_id", ["eq"=>$reference[0]])
                                        ->getFirstItem();
                if (is_array($mailFetchItem) && count($mailFetchItem)) {
                    $ticketId = $this->_threadRepo->getTicketIdByThreadId($mailFetchItem->getThreadId());
                    $address = $message->getAddresses("sender");
                    $data['source'] = "email";
                    $data['who_is'] = "customer";
                    $data['fullname'] = $address['name'];
                    $data['query'] = $message->getMessageBody($isHtml);
                    if (isset($header->ccaddress)) {
                        $data['cc'] = $header->ccaddress;
                    }
                    if (isset($header->bccaddress)) {
                        $data['bcc'] = $header->bccaddress;
                    }
                    $data['thread_type'] = "reply";
                    $this->_eventsRepo->checkTicketEvent("reply", $ticketId, "customer");
                    $threadId = $this->_threadRepo->createThread($ticketId, $data);

                    $mailFetchData[$header->message_id]['thread_id'] = $threadId;
                    $mailFetchData[$header->message_id]['message_id'] = $header->message_id;
                    $mailFetchData[$header->message_id]['sender'] = $address['address'];
                    $mailFetchData[$header->message_id]['u_id'] = $message->getUid();
                    $this->saveMailMessageData($mailFetchData);
                    $count++;
                }
            }
        }
        return $count;
    }

    public function insertMultiple($table, $data)
    {
        try {
            $tableName = $this->resource->getTableName($table);
            return $this->connection->insertMultiple($tableName, $data);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        }
    }

    public function saveMailMessageData($bulkInsert)
    {
        $this->insertMultiple(self::TABLENAME, $bulkInsert);
    }
}
