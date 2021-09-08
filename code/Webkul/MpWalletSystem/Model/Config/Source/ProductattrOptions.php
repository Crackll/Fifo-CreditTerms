<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpWalletSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpWalletSystem\Model\Config\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\DB\Ddl\Table;
use Webkul\MpWalletSystem\Model\Walletcreditrules;

/**
 * Webkul MpWalletSystem Model Class
 */
class ProductattrOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get All Options function
     *
     * @return array
     */
    public function getAllOptions()
    {
        $this->options=[
            [
                'label'=>'Select Options',
                'value'=>''
            ],[
                'label'=>__('Product Credit Amount'),
                'value'=>Walletcreditrules::WALLET_CREDIT_PRODUCT_CONFIG_BASED_ON_PRODUCT
            ],[
                'label'=>__('Rules amount'),
                'value'=>Walletcreditrules::WALLET_CREDIT_PRODUCT_CONFIG_BASED_ON_RULE
            ]
        ];
        return $this->options;
    }
    
    /**
     * Get a text for option value
     *
     * @param  array
     * @return string
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
    
    /**
     * Retrieve flat column definition
     *
     * @return array
     */
    
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Custom Attribute Options  ' . $attributeCode . ' column',
            ],
        ];
    }
}
