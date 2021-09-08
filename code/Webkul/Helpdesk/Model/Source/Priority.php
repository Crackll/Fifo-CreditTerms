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
class Priority implements OptionSourceInterface
{
    /**
     * @var \Webkul\Helpdesk\Model\Type
     */
    protected $ticketPriority;

    /**
     * Constructor
     *
     * @param \Magento\Cms\Model\Page $cmsPage
     */
    public function __construct(\Webkul\Helpdesk\Model\TicketsPriority $ticketPriority)
    {
        $this->ticketPriority = $ticketPriority;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->ticketPriority->toOptionArray();
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
