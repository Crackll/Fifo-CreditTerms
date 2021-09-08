<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_CustomerApproval
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\CustomerApproval\Model;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;

class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const PENDING = '0';
    const APPROVED = '1';
    const REJECTED = '2';
    
    /**
     * Get all options
     *
     * @return array
     */
    public function getAllOptions()
    {
        /* your Attribute options list*/
        $this->_options=[
            ['label'=>__('Pending'), 'value'=>'0'],
            ['label'=>__('Approve'), 'value'=>'1'],
            ['label'=>__('Reject'), 'value'=>'2']
        ];
        return $this->_options;
    }

    /**
     * Get a text for option value
     *
     * @param  string|integer $value
     * @return string|bool
     */
    public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
}
