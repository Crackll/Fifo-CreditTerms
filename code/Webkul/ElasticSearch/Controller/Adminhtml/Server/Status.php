<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ElasticSearch
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\ElasticSearch\Controller\Adminhtml\Server;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

use Magento\Ui\Component\MassAction\Filter;

/**
 * Webkul ElasticSearch Check Connection Status
 */
class Status extends AbstractServer
{

    public function execute()
    {
        try {

            $this->_elasticAdapter->connect();
            return $this->getJsonResponse(['message' => __("connection working"), 'success' => 1]);
        } catch (\Exception $e) {
            return $this->getJsonResponse(['message' => $e->getMessage(), 'error' => 1]);
        }
    }
}
