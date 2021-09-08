<?php
/**
 * Webkul MpDailyDeal CatalogProductSaveBefore Observer.
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpDailyDeal\Rewrite\Ui\DataProvider;

class Attributes extends \Magento\ConfigurableProduct\Ui\DataProvider\Attributes
{
    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $items = [];
        $skippedItems = 0;
        foreach ($this->getCollection()->getItems() as $attribute) {
            $dealAttributes = ['deal_discount_type', 'deal_status'];
            if ($this->configurableAttributeHandler->isAttributeApplicable($attribute)
                && !in_array($attribute->getAttributeCode(), $dealAttributes)) {
                $items[] = $attribute->toArray();
            } else {
                $skippedItems++;
            }
        }
        return [
            'totalRecords' => $this->collection->getSize() - $skippedItems,
            'items' => $items
        ];
    }
}
