<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Block\Adminhtml\Businesshours\Edit\Tab;

class HoursTemplate extends \Magento\Config\Block\System\Config\Form\Field
{
    const TEMPLATE = 'Webkul_Helpdesk::businesshours/edit/tab/hours.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $contex
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\Helpdesk\Model\BusinesshoursFactory $businesshoursFactory,
        \Webkul\Helpdesk\Model\Source\Days $daysModel,
        \Webkul\Helpdesk\Model\Source\TimeInterval $timeIntervalModel,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->_businesshoursFactory = $businesshoursFactory;
        $this->_daysModel = $daysModel;
        $this->_timeIntervalModel = $timeIntervalModel;
        $this->serializer = $serializer;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Set template to itself.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::TEMPLATE);
        }
        return $this;
    }

    //get Current Business Hours
    public function getCurrentBusinessHours($id)
    {
        return $this->_businesshoursFactory->create()->load($id);
    }

    public function getTimeOptionHtml($timeSelected)
    {
        $time = $this->getTimeInterval();
        $html = "";
        foreach ($time as $value) {
            if ($timeSelected == $value) {
                $html = $html."<option selected='selected' value='".$value."'>".$value."</option>";
            } else {
                $html = $html."<option value='".$value."'>".$value."</option>";
            }
        }
        return $html;
    }

    //get Days array
    public function getDays()
    {
        return $this->_daysModel->toOptionArray();
    }

    //get Days array
    public function getTimeInterval()
    {
        return $this->_timeIntervalModel->toOptionArray();
    }

    public function getSerializer()
    {
        return $this->serializer;
    }

    public function getJsonData()
    {
        return $this->jsonHelper;
    }
}
