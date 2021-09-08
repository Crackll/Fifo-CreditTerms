<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_ElasticSearch
 * @author Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\ElasticSearch\Model\Cron;

/**
 * Class reindex command
 */
class Reindex
{

    /**
     * @var \Webkul\ElasticSearch\Model\Command\Indexer
     */
    protected $_indexer;

    /**
     * process index
     *
     * @return void
     */
    public function process()
    {
        $indexer = \Magento\Framework\App\ObjectManager::getInstance()
        ->get(\Webkul\ElasticSearch\Model\Command\Indexer::class);
        $this->_indexer = $indexer;
        $this->_indexer->doReindex('all', 0, null);
    }
}
