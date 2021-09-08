<?php
/**
 * Webkul MpAuction detail controller.
 * @category  Webkul
 * @package   Webkul_MpAuction
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAuction\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;

class DataUpdated extends Action
{
    /**
     * @var \Webkul\MpAuction\Helper\Data
     */
    protected $_datahelper;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Helper\Data $jsonData,
        \Webkul\MpAuction\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->jsonData = $jsonData;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $productId = $this->getRequest()->getParam('id');
            $this->helper->cleanByTags($productId);
            $this->getResponse()->setHeader('Content-type', 'application/javascript');
            $this->getResponse()->setBody($this->jsonData
                ->jsonEncode(
                    [
                        'success' => 1
                    ]
                ));
        } catch (\Exception $e) {
            $this->getResponse()->setHeader('Content-type', 'application/javascript');
            $this->getResponse()->setBody($this->jsonData
                ->jsonEncode(
                    [
                        'success' => 0
                    ]
                ));
        }
    }
}
