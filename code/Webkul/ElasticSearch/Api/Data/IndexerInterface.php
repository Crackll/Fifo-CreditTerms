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
namespace Webkul\ElasticSearch\Api\Data;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

interface IndexerInterface
{
    const ENTITY_ID = 'id';
    const INDEX = 'index';
    const INDEX_TYPE = 'index_type';
    const STATUS = 'status';
    const UPDATED_AT = 'updated_at';
    const ATTRIBUTES = 'attributes';

    const REINDEX_REQUIRED = 0;
    const INDEX_READY = 1;

    const UPDATE_ON_SAVE = 1;
    const UPDATE_ON_SHEDULE = 2;
}
