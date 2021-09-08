<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_WebApplicationFirewall
 * @author Webkul
 * @copyright Copyright (c) WebkulSoftware Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 *
 */

namespace Webkul\WebApplicationFirewall\Controller\Adminhtml\Directory;

/**
 * WAF Scan class
 * TODO
 */
class Scan extends \Magento\Backend\App\Action
{

    protected $jsonHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Webkul\WebApplicationFirewall\Api\Data\ScanDirectoriesInterfaceFactory $scanDirectoriesFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Webkul\WebApplicationFirewall\Api\Data\ScanDirectoriesInterfaceFactory $scanDirectoriesFactory
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->scanDirectoriesFactory = $scanDirectoriesFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $credentials = null;
        $httpBadRequestCode = 400;

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();

        try {
            //$logData = $this->pronLog->getImportLog();
            return $this->jsonResponse(['pronolog']);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response = [
                'message' => $e->getMessage(),
                'response' => [
                    'continue' => false,
                    'error' => true
                ]
            ];
            return $this->jsonResponse($response);
        } catch (\Exception $e) {
            $response = [
                'message' => $e->getMessage(),
                'response' => [
                    'continue' => false,
                    'error' => true
                ]
            ];
            return $this->jsonResponse($response);
        }
    }

    /**
     * Create json response
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }
}
