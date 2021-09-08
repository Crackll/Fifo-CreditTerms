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

class Start extends \Magento\Backend\App\Action
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
     * @var \Magento\Backend\Model\Auth\SessionFactory
     */
    private $authSessionFactory;

    /**
     * @var AgentDataFactory
     */
    protected $agentDataFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSessionFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param AgentDataFactory $agentDataFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Backend\Model\Auth\SessionFactory $authSessionFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Shell $shell,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        AgentDataFactory $agentDataFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->authSessionFactory = $authSessionFactory;
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
        try {
            $response->setError(false);
            if (!$this->isServerRunning()) {
                $response->setRoot($this->directoryList->getRoot());
                $rootPath = $this->directoryList->getRoot();
                $node = $this->shell->execute('whereis node');
                $nodePath = explode(' ', $node);
                if (!isset($nodePath[1])) {
                    $node = $this->shell->execute('whereis nodejs');
                    $nodePath = explode(' ', $node);
                }
                if (isset($nodePath[1])) {
                    $this->shell->execute($nodePath[1].' '.$rootPath.'/app.js' . " > /dev/null &");
                    $response->setMessage(
                        __('Server Running.')
                    );
                } else {
                    $response->setError(true);
                    $response->setMessage(
                        __('Node path can not be found, make sure Node is installed on this server.')
                    );
                }
            } elseif (!$response->getError()) {
                $response->setMessage(
                    __('Node server already running.')
                );
            }

            $agentId = $this->authSessionFactory->create()->getUser()->getId();
            $agentDataModel = $this->agentDataFactory->create()
                ->getCollection();
            $entityId = 0;
            if ($agentDataModel->getSize()) {
                $entityId = $agentDataModel->getFirstItem()->getEntityId();
            }
            if ($entityId) {
                $agentDataModel = $this->agentDataFactory->create()->load($entityId);
                $agentDataModel->setAgentId($agentId);
                $agentDataModel->setId($entityId);
                $agentDataModel->save();
            } else {
                $user = $this->authSessionFactory->create()->getUser();
                $agentDataModel = $this->agentDataFactory->create()
                                                ->setAgentId($agentId)
                                                ->setAgentUniqueId($this->generateUniqueId())
                                                ->setAgentEmail($user->getEmail())
                                                ->setAgentName($user->getFirstName(). ' '.$user->getLastName())
                                                ->save();
            }
        } catch (\Exception $e) {
            $response->setError(true);
            $response->setMessage(
                __('Something went wrong.')
            );
        }
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }

    /**
     * check if the node server is already running on a specific port.
     *
     * @return bool
     */
    public function isServerRunning()
    {
        $result = false;
        $host = $this->getRequest()->getParam('hostname');
        $port = $this->getRequest()->getParam('port');
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

    /**
     * Generate Unique Id
     *
     * @return int
     */
    public function generateUniqueId()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pass = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        $id = 'wk'.implode($pass);
        return $id;
    }
}
