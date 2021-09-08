<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpPriceList
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpPriceList\Model\Config;

class Categories implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->create(\Magento\Catalog\Model\Category::class)->getCollection();
        $collection->addAttributeToSelect("name");
        $collection->addAttributeToFilter("level", ["gteq" => 2]);
        $firstLevel = [];
        $secondLevel = [];
        $thirdLevel = [];
        $allCategories = [];

        $data = [];
        foreach ($collection as $category) {
            $parentId = $category->getParentId();
            $id = $category->getEntityId();
            $level = $category->getLevel();
            if ($level == 2) {
                $firstLevel[$parentId][] = $id;
            }
            if ($level == 3) {
                $secondLevel[$parentId][] = $id;
            }
            if ($level == 4) {
                $thirdLevel[$parentId][] = $id;
            }
            $allCategories[$id] = $category->getName();
        }
        foreach ($firstLevel as $flp => $flc) {
            foreach ($flc as $slc) {
                $data[] = ['value' => $slc, 'label' => $allCategories[$slc]];
                if (array_key_exists($slc, $secondLevel)) {
                    $data = $this->secondLevelCheck($secondLevel, $slc, $allCategories, $thirdLevel, $data);
                }
            }
        }
        return $data;
    }

    /**
     * Second level category check
     *
     * @param array $secondLevel
     * @param array $slc
     * @param array $allCategories
     * @param array $thirdLevel
     * @param array $data
     * @return array
     */
    protected function secondLevelCheck($secondLevel, $slc, $allCategories, $thirdLevel, $data)
    {
        foreach ($secondLevel[$slc] as $slcId) {
            $data[] = ['value' => $slcId, 'label' => "- ".$allCategories[$slcId]];
            if (array_key_exists($slcId, $thirdLevel)) {
                foreach ($thirdLevel[$slcId] as $tlcId) {
                    $data[] = ['value' => $tlcId, 'label' => "-- ".$allCategories[$tlcId]];
                }
            }
        }
        return $data;
    }
}
