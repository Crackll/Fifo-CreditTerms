<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Helpdesk
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Helpdesk\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class Months implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [1 => "Jan",2 => "Feb",3 => "Mar",4 => "Apr",5 => "May",6 => "Jun",7 => "Jul",
        8 => "Aug",9 => "Sep",10 => "Oct",11 => "Nov",12 => "Dec"];
    }
}
