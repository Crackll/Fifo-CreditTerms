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
namespace Webkul\Helpdesk\Model;

class Activitypriority
{
    /**
     * toOptionArray Returns priority Options
     * @return Array priority Options
     */
    public function toOptionArray()
    {
        return $data =  [
                    ['value'=>'low', 'label'=>'Low'],
                    ['value'=>'medium', 'label'=>'Medium'],
                    ['value'=>'high', 'label'=>'High']
                ];
    }
}
