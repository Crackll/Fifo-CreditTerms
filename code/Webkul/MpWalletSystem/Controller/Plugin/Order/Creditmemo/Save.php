<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Controller\Plugin\Order\Creditmemo;

/**
 * Webkul MpWalletSystem Controller
 */
class Save
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Initialize dependencies
     *
     * @param MagentoFrameworkAppRequestHttp $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }
    
    public function beforeExecute(
        \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Save $subject
    ) {
        $params = $this->request->getPost();
        $params->creditmemo['do_offline']=1;
        $this->request->setPost($params);
        $params = $this->request->getPost();
    }
}
