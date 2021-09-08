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

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Driver\File;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AttachmentRepository implements \Webkul\Helpdesk\Api\AttachmentRepositoryInterface
{
    /**
     * @var \Webkul\Helpdesk\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Webkul\Helpdesk\Model\CustomerFactory
     */
    protected $_helpdeskCustomerFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @var \Webkul\Helpdesk\Logger\HelpdeskLogger
     */
    protected $_helpdeskLogger;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploader;

    /**
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var File
     */
    protected $_file;

    /**
     * TicketsRepository constructor.
     * @param \Webkul\Helpdesk\Helper\Data $helper
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Webkul\Helpdesk\Helper\Data $helper,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Webkul\Helpdesk\Logger\HelpdeskLogger $helpdeskLogger,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        Filesystem $filesystem,
        File $file,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Webkul\Helpdesk\Model\ThreadFactory $threadFactory
    ) {
        $this->_helper = $helper;
        $this->_authSession = $authSession;
        $this->_helpdeskLogger = $helpdeskLogger;
        $this->_fileUploader = $fileUploaderFactory;
        $this->_filesystem = $filesystem;
        $this->_file = $file;
        $this->_fileDriver = $fileDriver;
        $this->_threadFactory = $threadFactory;
    }

    /**
     * SaveAttachment Save ticket attachment
     * @param Int $ticket_id Ticket Id
     * @param Int $threadId Thread Id
     * @param Array $FILES Attachment Files
     */
    public function saveAttachment($ticket_id, $threadId)
    {
        $path = $this->_helper->getMediaPath()."helpdesk/websiteattachment/".$threadId."/";
        if (!$this->_fileDriver->isExists($path)) {
            $this->_file->createDirectory($path);
        }
        $error = 0;
        $maxSize = $this->_helper->getUploadFileSize();
        $allowedExt = $this->_helper->getConfigAllowedextensions();
        $directory = $this->_filesystem->getDirectoryRead(DirectoryList::SYS_TMP);
        $allowedExt = explode(",", $allowedExt);
        $fileUploader = $this->_fileUploader->create(['fileId' => 'fupld']);
        if ($maxSize > 0 && $fileUploader->getFileSize()/(1000000) > $maxSize
        ) {
            throw new LocalizedException(
                __('The file you\'re uploading exceeds the max size limit of %1 MB.', $maxSize)
            );
        }
        $fileUploader->setAllowedExtensions($allowedExt);
        $fileUploader->setAllowRenameFiles(true);
        $fileUploader->setFilesDispersion(false);
        $result = $fileUploader->save($path);
        if (is_array($result)) {
            $this->_threadFactory->create()->load($threadId)->setAttachment(1)->save();
        }
    }
}
