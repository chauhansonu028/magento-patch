<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\DataProvider;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Api\Data\FileScopeInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\File\ResourceModel\CollectionFactory;
use Amasty\ProductAttachment\Model\File\ResourceModel\File;
use Amasty\ProductAttachment\Model\Icon\GetIconForFile;
use Magento\Framework\App\RequestInterface;

class InsertListing extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Amasty\ProductAttachment\Model\File\ResourceModel\Collection
     */
    protected $collection;

    /**
     * @var GetIconForFile
     */
    private $getIconForFile;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var int
     */
    private $totalRecordPlus;

    /**
     * @var array
     */
    private $fieldsForCustomFilter = [
        FileScopeInterface::LABEL,
        FileScopeInterface::FILENAME
    ];

    public function __construct(
        CollectionFactory $collectionFactory,
        GetIconForFile $getIconForFile,
        RequestInterface $request,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->collection->addInsertListingFileData($request->getParam('store_id', 0));
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->getIconForFile = $getIconForFile;
        $this->request = $request;
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        $needDefaultFilter = true;

        if ($filter->getField() == FileInterface::FILE_ID && $filter->getConditionType() == 'nin') {
            if ($filter->getValue()) {
                $this->totalRecordPlus = count($filter->getValue());
            }
        } elseif ($this->request->getParam('store_id', 0)
            && in_array($filter->getField(), $this->fieldsForCustomFilter)
        ) {
            $condition = '(' . File::FILE_STORE_TABLE_NAME . '_store.' . $filter->getField()
                . ' IS NOT NULL'
                . ' AND ' . File::FILE_STORE_TABLE_NAME . '_store.' . $filter->getField()
                . ' ' . $filter->getConditionType() . ' ?)'
                . ' OR (' . File::FILE_STORE_TABLE_NAME . '_store.' . $filter->getField() . ' IS NULL'
                . ' AND ' . File::FILE_STORE_TABLE_NAME . '.' . $filter->getField() . ' '
                . $filter->getConditionType() . ' ?)';
            $this->getCollection()->getSelect()
                ->where(
                    $condition,
                    $filter->getValue()
                );
            $needDefaultFilter = false;
        }

        if ($needDefaultFilter) {
            parent::addFilter($filter);
        }
    }

    public function getData()
    {
        $data = parent::getData();
        if (!empty($data['items'])) {
            foreach ($data['items'] as &$item) {
                $item['icon_src'] = $this->getIconForFile->byFileExtension(
                    $item[FileInterface::EXTENSION],
                    (int)$item[FileInterface::ATTACHMENT_TYPE]
                ) ?: false;
                $item['show_file_id'] = $item[FileInterface::FILE_ID];
                $item[FileInterface::SIZE] = $this->getReadableFileSize((int)$item[FileInterface::SIZE]);
                foreach (RegistryConstants::USE_DEFAULT_FIELDS as $field) {
                    $item[$field . '_use_defaults'] = "1";
                }
            }
        }
        if ($this->totalRecordPlus) {
            $data['totalRecords'] += $this->totalRecordPlus;
        }
        return $data;
    }

    /**
     * @param int $bytes
     *
     * @return string
     */
    public function getReadableFileSize($bytes = 0)
    {
        $size   = ['B', 'kB', 'MB', 'GB', 'TB'];
        $factor = (int)floor((strlen($bytes) - 1) / 3);
        if (isset($size[$factor])) {
            $bytes = sprintf("%.2f", $bytes / pow(1024, $factor)) . ' ' . $size[$factor];
        }

        return $bytes;
    }
}
