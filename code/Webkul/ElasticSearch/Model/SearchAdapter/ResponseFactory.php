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
namespace  Webkul\ElasticSearch\Model\SearchAdapter;

/**
 * Response Factory
 */
class ResponseFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var AggregationFactory $aggregationFactory
     */
    protected $aggregationFactory;

    protected $documentFactory;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param DocumentFactory $documentFactory
     * @param AggregationFactory $aggregationFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Elasticsearch\SearchAdapter\AggregationFactory  $aggregationFactory,
        \Magento\Elasticsearch\SearchAdapter\DocumentFactory  $documentFactory
    ) {
        $this->objectManager = $objectManager;
        $this->aggregationFactory = $aggregationFactory;
        $this->documentFactory = $documentFactory;
    }

    /**
     * Create Query Response instance
     *
     * @param mixed $rawResponse
     * @return \Magento\Framework\Search\Response\QueryResponse
     */
    public function create($rawResponse)
    {
        $documents = [];
        foreach ($rawResponse['documents'] as $rawDocument) {
            $documents[] = $this->documentFactory->create($rawDocument);
        }
        $aggregations = $this->aggregationFactory->create($rawResponse['aggregations']);
        return $this->objectManager->create(
            \Magento\Framework\Search\Response\QueryResponse::class,
            [
                'documents' => $documents,
                'aggregations' => $aggregations,
                'total' => $rawResponse['total']
            ]
        );
    }
}
