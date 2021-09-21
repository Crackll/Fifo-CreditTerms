<?php

namespace Fifo\CreditTerms\Controller\Payment;

use Magento\Framework\App\Action\Context;

class Regions extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    protected $_regionColFactory;

    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Directory\Model\RegionFactory $regionColFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_regionColFactory = $regionColFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $regions = $this->_regionColFactory->create()->getCollection()
                ->addFieldToFilter('country_id',$this->getRequest()->getParam('country_id'));
            $html = '';
            if(count($regions) > 0)
            {
                $html .= '<select name="region" id="state" class="required-entry validate-state">';
                $html.='<option selected="selected" value="">Please select a region.</option>';
                foreach($regions as $state)
                {
                    $html .= '<option  value="'.$state->getName().'">'.$state->getName().'.</option>';
                }
                $html .= "</select>";
            }else{
                $html .= '<input name="region" id="state" class="required-entry input-text" type="text"/>';
            }
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData(['success' => true,'value'=>$html]);
            return $resultJson;
        }
        catch (\Magento\Framework\Exception\LocalizedException $e) {
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData(['success' => false,'value'=>$e->getMessage()]);
            $resultJson->setData($e->getMessage());
            return $resultJson;
        }
    }
}