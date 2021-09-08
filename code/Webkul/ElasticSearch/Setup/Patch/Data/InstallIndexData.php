<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_ElasticSearch
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\ElasticSearch\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class InstallIndexData implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var Webkul\ElasticSearch\Model\IndexerFactory
     */
    private $_indexFatory;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $_eavAttributesRepo;

    /**
     * @var Magento\Framework\Api\SearchCriteriaInterface
     */
    private $_searchCreteria;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \Webkul\ElasticSearch\Model\IndexerFactory $indexFatory,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCreteria,
        \Magento\Eav\Api\AttributeRepositoryInterface $eavAttributesRepo
    )
    {
        $this->_indexFatory = $indexFatory;
        $this->_eavAttributesRepo= $eavAttributesRepo;
        $this->_searchCreteria = $searchCreteria;
        $this->moduleDataSetup = $moduleDataSetup;
        // ->getConnection()->startSetup();
        // $connection = $this->moduleDataSetup->getConnection();

    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $attributeInfo = $this->_eavAttributesRepo->getList(ProductAttributeInterface::ENTITY_TYPE_CODE, $this->_searchCreteria);
        $searchableAttrbutes = [];
        $fieldMapping = [
            'select' => 'array',
            'text' => 'text',
            'price' => 'double_range',
            'date' => 'date',
            'textarea' => 'text',
            'multiselect' => 'array',
            'boolean' => 'boolean',
            'multiline' => 'array'
        ];

        foreach ($attributeInfo->getItems() as $attributes) {
            $attributeId = $attributes->getAttributeId();
            if ($attributeId && ($attributes->getIsSearchable() || $attributes->getIsFilterable())) {
                $attributeType = isset($fieldMapping[$attributes->getFrontendInput()])?
                $fieldMapping[$attributes->getFrontendInput()]:'text';
                $searchableAttrbutes[$attributeId] = [
                    'code' => $attributes->getAttributeCode(),
                    'type' => $attributeType,
                    'boost' => $attributes->getSearchWeight(),
                    'is_filterable' => $attributes->getIsFilterable(),
                    'is_searchable' => $attributes->getIsSearchable(),
                    'attribute' => 'default'
                ];

            }
        }

        $data = [];
        array_push($data, [
            'index' => 'catalog',
            'type' => 'product',
            'status' => \Webkul\ElasticSearch\Api\Data\IndexerInterface::REINDEX_REQUIRED,
            'mode' => \Webkul\ElasticSearch\Api\Data\IndexerInterface::UPDATE_ON_SAVE
        ]);

        array_push($data, [
            'index' => 'catalog-category',
            'type' => 'category',
            'status' => \Webkul\ElasticSearch\Api\Data\IndexerInterface::REINDEX_REQUIRED,
            'mode' => \Webkul\ElasticSearch\Api\Data\IndexerInterface::UPDATE_ON_SAVE
        ]);

        array_push($data, [
            'index' => 'cms',
            'type' => 'pages',
            'status' => \Webkul\ElasticSearch\Api\Data\IndexerInterface::REINDEX_REQUIRED,
            'mode' => \Webkul\ElasticSearch\Api\Data\IndexerInterface::UPDATE_ON_SAVE
        ]);
        foreach ($data as $index) {
             $seachAttributes = $searchableAttrbutes;
            if ($index['type'] == 'category') {
                $seachAttributes = [
                    'name' => [
                        'code' => 'name',
                        'type' => 'text',
                        'boost' => 5,
                        'attribute' => 'default',
                    ],
                    'description'=> [
                        'code' => 'description',
                        'type' => 'text',
                        'boost' => 4,
                        'attribute' => 'default'
                    ],
                    'products' => [
                        'code' => 'products',
                        'type' => 'array',
                        'attribute' => 'custom',
                        'boost' => 1
                    ],
                    'path' => [
                        'code' => 'path',
                        'type' => 'array',
                        'boost' => 1,
                        'attribute' => 'default'
                    ],
                ];
            } elseif ($index['type'] == 'pages') {
                $seachAttributes = [
                    'title' => [
                        'code' => 'title',
                        'type' => 'text',
                        'boost' => 5,
                        'attribute' => 'default'
                    ],
                    'content'=> [
                        'code' => 'content',
                        'type' => 'text',
                        'boost' => 1,
                        'attribute' => 'default'
                    ],
                    'store_id'=> [
                        'code' => 'store_id',
                        'type' => 'array',
                        'boost' => 1,
                        'attribute' => 'default'
                    ]
                ];
            }
            $indexModel = $this->_indexFatory->create()->setData($index);
            $indexModel->setAttributes($seachAttributes);
            $indexModel->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
