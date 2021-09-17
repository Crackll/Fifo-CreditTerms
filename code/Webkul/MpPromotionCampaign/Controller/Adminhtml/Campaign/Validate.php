<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPromotionCampaign
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPromotionCampaign\Controller\Adminhtml\Campaign;

use Magento\Backend\App\Action;

/**
 * Campaign validate
 */
class Validate extends Action
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\DateTime
     */
    protected $_dateFilter;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\Filter\DateTime $dateFilter
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Stdlib\DateTime\Filter\DateTime $dateFilter,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->_dateFilter = $dateFilter;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);
        try {
            $data = $this->getRequest()->getParam('general');
            if (!empty($data['start_date']) && !empty($data['end_date'])) {
                $inputFilter = new \Zend_Filter_Input(
                    [
                        'start_date' => $this->_dateFilter,
                        'end_date' => $this->_dateFilter
                    ],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $fromDate = strtotime($data['start_date']);
                $toDate = strtotime($data['end_date']);
                if ($toDate < $fromDate) {
                    $response->setError(true);
                    $response->setMessages([__('Make sure the To Date is later than the From Date.')]);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response->setError(true);
            $response->setMessages([$e->getMessage()]);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $response->setError(true);
            $response->setMessages([$e->getMessage()]);
        }

        return $this->resultJsonFactory->create()->setData($response);
    }
}
