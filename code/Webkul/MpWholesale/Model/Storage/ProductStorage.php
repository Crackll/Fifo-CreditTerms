<?php
/**
 * @category   Webkul
 * @package    Webkul_MpWholesale
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpWholesale\Model\Storage;

use Magento\Framework\App\ResourceConnection;

class ProductStorage
{
    /**
     * DB Storage table name
     */
    const TABLE_NAME = 'mpwholesale_product_details';

    /**
     * Code of "Integrity constraint violation: 1062 Duplicate entry" error
     */
    const ERROR_CODE_DUPLICATE_ENTRY = 23000;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
    }

    /**
     * Insert multiple
     * @param array $data
     * @param string $tableName
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Exception
     */
    public function insertMultiple($data, $tableName = self::TABLE_NAME)
    {
        try {
            $tableName = $this->resource->getTableName($tableName);
            return $this->connection->insertMultiple($tableName, $data);
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }
}
