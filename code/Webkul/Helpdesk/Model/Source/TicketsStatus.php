<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class TicketsStatus implements OptionSourceInterface
{
    /**
     * @var \Webkul\Helpdesk\Model\TicketsStatus
     */
    protected $ticketStatus;

    /**
     * Constructor
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     */
    public function __construct(\Webkul\Helpdesk\Model\TicketsStatus $ticketStatus)
    {
        $this->ticketStatus = $ticketStatus;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->ticketStatus->toOptionArray();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value['label'],
                'value' => $value['value'],
            ];
        }
        return $options;
    }
}
