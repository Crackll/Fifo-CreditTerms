<?php
/**
 * Webkul_MpDailyDeal ListProduct toolbar block.
 * @category  Webkul
 * @package   Webkul_MpDailyDeal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpDailyDeal\Block\Product\ListProduct;

class Toolbar extends \Magento\Catalog\Block\Product\ProductList\Toolbar
{
    /**
     * Set collection to pager
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $today = $this->converToTz($this->_localeDate->date()->format('Y/m/d H:i:s'));
        $collection= $collection->addAttributeToSelect('*')
             ->addAttributeToFilter('deal_status', 1)
             ->addAttributeToFilter('deal_from_date', ['lt'=>$today])
             ->addAttributeToFilter('deal_to_date', ['gt'=>$today]);
        $this->_collection = $collection;
        $this->_collection->setCurPage($this->getCurrentPage());
        /* we need to set pagination only
         if passed value integer and more that 0*/
        $limit = (int)$this->getLimit();
        if ($limit) {
            $this->_collection->setPageSize($limit);
        }
        if ($this->getCurrentOrder()) {
            $this->_collection->setOrder(
                $this->getCurrentOrder(),
                $this->getCurrentDirection()
            );
        }
        return $this;
    }
    public function converToTz($dateTime = "", $fromTz = '', $toTz = '')
    {
        if (!$fromTz) {
            $fromTz = $this->_localeDate->getConfigTimezone();
        }
        // timezone by php friendly values
        $date = new \DateTime($dateTime, new \DateTimeZone($fromTz));
        $date->setTimezone(new \DateTimeZone('UTC'));
        $dateTime = $date->format('Y/m/d H:i:s');
        return $dateTime;
    }
}
