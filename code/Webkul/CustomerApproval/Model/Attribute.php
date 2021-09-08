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

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

/**
 * Catalog Product Mass processing resource model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Attribute extends \Magento\Eav\Model\Entity\AbstractEntity
{
    /**
     * Initialize connection
     *
     * @return void
     */
    protected function _construct()
    {
        $resource = $this->_resource;
        $this->setType(
            \Magento\Customer\Model\Customer::ENTITY
        )->setConnection(
            $resource->getConnection('customer')
        );
    }

    /**
     * Update attribute values for entity list per store
     *
     * @param  array $entityIds
     * @param  array $attrData
     * @param  int   $storeId
     * @return $this
     * @throws \Exception
     */
    public function updateAttributes($entityIds, $attrData)
    {
        $object = new \Magento\Framework\DataObject();

        $this->getConnection()->beginTransaction();
        try {
            foreach ($attrData as $attrCode => $value) {
                $attribute = $this->getAttribute($attrCode);
                if (!$attribute->getAttributeId()) {
                    continue;
                }

                $i = 0;
                foreach ($entityIds as $entityId) {
                    $i++;
                    $object->setId($entityId);
                    $object->setEntityId($entityId);
                    // collect data for save
                    $this->_saveAttributeValue($object, $attribute, $value);
                    // save collected data every 1000 rows
                    if ($i % 1000 == 0) {
                        $this->_processAttributeValues();
                    }
                }
                $this->_processAttributeValues();
            }
            $this->getConnection()->commit();
        } catch (\Exception $e) {
            $this->getConnection()->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Insert or Update attribute data
     *
     * @param  \Magento\Catalog\Model\AbstractModel $object
     * @param  AbstractAttribute                    $attribute
     * @param  mixed                                $value
     * @return $this
     */
    protected function _saveAttributeValue($object, $attribute, $value)
    {
        $connection = $this->getConnection();
        $table = $attribute->getBackend()->getTable();

        $entityId = $this->resolveEntityId($object->getId(), $table);

        $data = new \Magento\Framework\DataObject(
            [
                'attribute_id' => $attribute->getAttributeId(),
                $this->getLinkField() => $entityId,
                'value' => $this->_prepareValueForSave($value, $attribute),
            ]
        );
        $bind = $this->_prepareDataForTable($data, $table);
        $this->_attributeValuesToSave[$table][] = $bind;
        return $this;
    }

    /**
     * @param int $entityId
     * @return int
     */
    protected function resolveEntityId($entityId)
    {
        if ($this->getIdFieldName() == $this->getLinkField()) {
            return $entityId;
        }
        $select = $this->getConnection()->select();
        $tableName = $this->_resource->getTableName('customer_entity');
        $select->from($tableName, [$this->getLinkField()])
            ->where('entity_id = ?', $entityId);
        return $this->getConnection()->fetchOne($select);
    }
}
