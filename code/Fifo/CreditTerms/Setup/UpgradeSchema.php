<?php

namespace Fifo\CreditTerms\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        $tableName = $installer->getTable('fifo_creditterms_applications');
        $connection = $installer->getConnection();

        if(version_compare($context->getVersion(), '1.0.1', '<')) {
            $connection->addColumn(
                $tableName,
                'credit_term_category',
                [
                    'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'default' => null,
                    'comment' => 'Credit Term Category'
                ]
            );
            $connection->addColumn(
                $tableName,
                'credit_term_days',
                [
                    'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'default' => null,
                    'comment' => 'Credit Term Days'
                ]
            );
            $connection->addColumn(
                $tableName,
                'credit_term_limit',
                [
                    'type'    => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'default' => null,
                    'comment' => 'Credit Term Limit'
                ]
            );

        }
    }
}
