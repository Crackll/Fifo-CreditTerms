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

class HolidaysTemplate extends \Magento\Config\Block\System\Config\Form\Field
{
    const TEMPLATE = 'Webkul_Helpdesk::businesshours/edit/tab/holidays.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $contex
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\Helpdesk\Model\BusinesshoursFactory $businesshoursFactory,
        \Webkul\Helpdesk\Model\Source\Months $monthsModel,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $data = []
    ) {
        $this->_businesshoursFactory = $businesshoursFactory;
        $this->_monthsModel = $monthsModel;
        $this->serializer = $serializer;
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

    //get Months array
    public function getMonths()
    {
        return $this->_monthsModel->toOptionArray();
    }

    public function getSerializer()
    {
        return $this->serializer;
    }
}
