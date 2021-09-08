<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Webkul\MpRewardSystem\Model\Export;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Convert\Excel;
use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\ConvertToXml as UiXml;

class ConvertToXml extends UiXml
{
    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;

    /**
     * @var ExcelFactory
     */
    protected $excelFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var SearchResultIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param MetadataProvider $metadataProvider
     * @param ExcelFactory $excelFactory
     * @param SearchResultIteratorFactory $iteratorFactory
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        \Magento\Ui\Model\Export\MetadataProvider $metadataProvider,
        ExcelFactory $excelFactory,
        \Magento\Ui\Model\Export\SearchResultIteratorFactory $iteratorFactory,
        CustomerFactory $customerFactory
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->metadataProvider = $metadataProvider;
        $this->excelFactory = $excelFactory;
        $this->iteratorFactory = $iteratorFactory;
        $this->_customerModel = $customerFactory;
        parent::__construct(
            $filesystem,
            $filter,
            $metadataProvider,
            $excelFactory,
            $iteratorFactory
        );
    }

    /**
     * Returns XML file
     *
     * @return array
     * @throws LocalizedException
     */
    public function getXmlFile()
    {
        $component = $this->filter->getComponent();

        $name = hash('md5', microtime());
        $file = 'export/' . $component->getName() . $name . '.xml';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();

        $component->getContext()->getDataProvider()->setLimit(0, 0);

        /** @var SearchResultInterface $searchResult */
        $searchResult = $component->getContext()->getDataProvider()->getSearchResult();

        /** @var DocumentInterface[] $searchResultItem */
        $searchResultItem = $searchResult->getItems();

        foreach ($searchResultItem as $key => $value) {
            # code...
            if ($component->getName() == "mprewardsystem_cartrules") {
                $customer = $this->_customerModel->create()
                    ->load($value->getSellerId());
                $CustName = $customer->getFirstname() . " " . $customer->getLastname();
                if ($value->getSellerId() == 0) {
                    $value->setSellerId("Admin");
                } else {
                    $value->setSellerId($CustName);
                }
                if ($value->getStatus()) {
                    $value->setStatus("Enabled");
                } else {
                    $value->setStatus("Disabled");
                }
            }
        }

        $this->prepareItems($component->getName(), $searchResultItem);

        /** @var SearchResultIterator $searchResultIterator */
        $searchResultIterator = $this->iteratorFactory->create(['items' => $searchResultItem]);
        /** @var Excel $excel */
        $excel = $this->excelFactory->create(
            [
                'iterator' => $searchResultIterator,
                'rowCallback' => [$this, 'getRowData'],
            ]
        );

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $excel->setDataHeader($this->metadataProvider->getHeaders($component));
        $excel->write($stream, $component->getName() . '.xml');

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true, // can delete file after use
        ];
    }
}
