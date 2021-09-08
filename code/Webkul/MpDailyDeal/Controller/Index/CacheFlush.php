<?php
/**
 * Webkul_MpDailyDeal Collection controller.
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpDailyDeal\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class CacheFlush extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Helper\Data $jsonData,
        \Webkul\MpDailyDeal\Helper\Data $helper
    ) {
        $this->jsonData = $jsonData;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $data = $this->getRequest()->getPostValue();
            if ($data && isset($data['ids'])) {
                if (is_array($data) && count($data)>0) {
                    $proids=explode(",", $data['ids']);
                    foreach ($proids as $ids):
                        $this->helper->cleanByTags($ids, 'P');
                    endforeach;
                } else {
                    $this->helper->cleanByTags($data['ids'], 'P');
                }
            }
            
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
                        'success' => 0,
                        'message' => __('Something went wrong in getting spin wheel.')
                    ]
                ));
        }
    }
}
