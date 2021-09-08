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

namespace Webkul\ElasticSearch\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class GreetingCommand
 */
class Reindex extends Command
{

    /**
     * Name argument
     */
    const TYPE_ARGUMENT = 'index-type';

    /**
     * Index Allow
     */
    const INDEX_ACTION = 'index-action';

    /**
     * Index Allow
     */
    const STORE_ID = 'store-id';

    /**
     * @var \Webkul\ElasticSearch\Model\Command\Indexer
     */
    protected $_indexer;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @param \Webkul\ElasticSearch\Model\Command\Indexer $indexer
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Webkul\ElasticSearch\Model\Command\Indexer $indexer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $appState
    ) {
        $this->_indexer = $indexer;
        $this->_storeManager = $storeManager;
        $this->appState = $appState;
        
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('elastic:index')
            ->setDescription('Elastic Search Index Management')
            ->setDefinition([
                new InputArgument(
                    self::TYPE_ARGUMENT,
                    InputArgument::OPTIONAL,
                    'Index Type optional valid options product, category, pages, all'
                ),
                new InputOption(
                    self::INDEX_ACTION,
                    '-a',
                    InputArgument::OPTIONAL,
                    'index action required between: reindex or remove'
                ),
                new InputOption(
                    self::STORE_ID,
                    '-s',
                    InputArgument::OPTIONAL,
                    'store id optional'
                ),
            ]);
            
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode("adminhtml");
        $type = $input->getArgument(self::TYPE_ARGUMENT);
        $action = $input->getOption(self::INDEX_ACTION);
        $storeId = (int) $input->getOption(self::STORE_ID);
        
        $type = $this->validateType($type);
        $action = $this->validateAction($action);
        $storeId = $this->validateStore($storeId);
        
        if ($action == 'reindex') {
            $this->reindex($type, $storeId, $output);
        } elseif ($action == 'remove') {
            $this->removeIndex($type, $storeId, $output);
        }
    }

    /**
     * validate type
     *
     * @param string $type
     * @return string
     * @throws InvalidArgumentException
     */
    private function validateType($type)
    {
        if ($type) {
            if (in_array($type, ["product", "category", "pages", "all"])) {
                return $type;
            } else {
                throw new \InvalidArgumentException(
                    'Invalid value for argument  '.self::TYPE_ARGUMENT.' given correct value should be product, pages, category, all'
                );
            }
        } else {
            return 'all';
        }
    }

    /**
     * validate action
     *
     * @param string $action
     * @return action
     * @throws InvalidArgumentException
     */
    private function validateAction($action)
    {
        $action = trim($action);
        if ($action) {
            if (!in_array($action, ["remove", "reindex"])) {
                throw new \InvalidArgumentException(
                    'Invalid value given for argument '.self::INDEX_ACTION.' correct value should be remove or reindex'
                );
            }
        } else {
            throw new \InvalidArgumentException('Argument '.self::INDEX_ACTION.' is required');
        }

        return $action;
    }

    /**
     * validate store
     *
     * @param int $store
     * @return int
     * @throws InvalidArgumentException
     */
    private function validateStore($store)
    {
        if ($store) {
            if (is_int($store)) {
                $s = $this->_storeManager->getStore($store);
                if (!$s->getId()) {
                    throw new \InvalidArgumentException('Argument '.self::STORE_ID.' is not a valid store id');
                }
                return $s->getId();
            } else {
                throw new \InvalidArgumentException('Argument '.self::STORE_ID.' must be integer');
            }
        } else {
            return 0;
        }
    }

    /**
     * reindex elastic data
     *
     * @return bool
     */
    private function reindex($type, $storeId, $output)
    {
        $this->_indexer->doReindex($type, $storeId, $output);
    }

    /**
     * remove elastic data
     *
     * @return bool
     */
    private function removeIndex($type, $storeId, $output)
    {
        $this->_indexer->doRemoveIndex($type, $storeId, $output);
    }
}
