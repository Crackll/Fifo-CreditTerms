<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MagentoChatSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MagentoChatSystem\Controller\Adminhtml\Server;

use Webkul\MagentoChatSystem\Model\AgentDataFactory;

class Stop extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var CustomerSession
     */
    private $authSession;

    /**
     * @var AgentDataFactory
     */
    protected $agentDataFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param AgentDataFactory $agentDataFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Shell $shell,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        AgentDataFactory $agentDataFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->authSession = $authSession;
        $this->directoryList = $directoryList;
        $this->agentDataFactory = $agentDataFactory;
        $this->shell = $shell;
        $this->fileDriver = $fileDriver;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);
        $data = $this->getRequest()->getParams();
        $response->setRoot($this->directoryList->getRoot());
        $rootPath = $this->directoryList->getRoot();
        $getUserPath = $this->shell->execute('whereis fuser');
        if ($getUserPath) {
            $getUserPath = explode(' ', $getUserPath);
            if (isset($getUserPath[1])) {
                $stopServer = $this->shell->execute($getUserPath[1].' -k '.$data['port'].'/tcp');
            }
            $agentId = $this->authSession->getUser()->getId();
            $agentDataModel = $this->agentDataFactory->create()
                ->getCollection()
                ->addFieldToFilter('agent_id', ['eq' => $agentId]);
            $entityId = 0;
            if ($agentDataModel->getSize()) {
                $entityId = $agentDataModel->getLastItem()->getEntityId();
            }
            if ($entityId) {
                $agentDataModel = $this->agentDataFactory->create()->load($entityId);
                $agentDataModel->setChatStatus(1);
                $agentDataModel->setId($entityId);
                $agentDataModel->save();
            }
        }
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }

    /**
     * Check Server Runing or not
     *
     * @param string $host
     * @param integer $port
     * @param integer $timeout
     * @return boolean
     */
    public function isServerRunning($host, $port = 80, $timeout = 6)
    {
        $result = false;
        try {
            $connection = fsockopen($host, $port);
            if (is_resource($connection)) {
                $result = true;
                $this->fileDriver->fileClose($connection);
            } else {
                $result = false;
            }
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }
}
