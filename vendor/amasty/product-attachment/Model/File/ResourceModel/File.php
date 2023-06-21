<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\ResourceModel;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Model\File\ResourceModel\Relation\HandlerInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class File extends AbstractDb
{
    public const TABLE_NAME = 'amasty_file';
    public const REPORT_TABLE_NAME = 'amasty_file_report';
    public const FILE_STORE_TABLE_NAME = 'amasty_file_store';
    public const FILE_STORE_CATEGORY_TABLE_NAME = 'amasty_file_store_category';
    public const FILE_STORE_PRODUCT_TABLE_NAME = 'amasty_file_store_product';
    public const FILE_STORE_CATEGORY_PRODUCT_TABLE_NAME = 'amasty_file_store_category_product';

    /**
     * @var array
     */
    private $readHandlers;

    /**
     * @var array
     */
    private $saveHandlers;

    public function __construct(
        Context $context,
        $connectionName = null,
        array $readHandlers = [],
        array $saveHandlers = []
    ) {
        parent::__construct($context, $connectionName);
        $this->checkHandlerInstance($readHandlers);
        $this->checkHandlerInstance($saveHandlers);
        $this->readHandlers = $readHandlers;
        $this->saveHandlers = $saveHandlers;
    }

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, FileInterface::FILE_ID);
    }

    public function load(AbstractModel $object, $value, $field = null)
    {
        parent::load($object, $value, $field);
        if ($object instanceof FileInterface) {
            foreach ($this->readHandlers as $handler) {
                $handler->execute($object);
            }
        }

        return $this;
    }

    public function save(AbstractModel $object)
    {
        try {
            parent::save($object);
            $this->beginTransaction();

            foreach ($this->saveHandlers as $handler) {
                $handler->execute($object);
            }

            $this->commit();
        } catch (\Exception $exception) {
            $this->rollBack();
            throw $exception;
        }

        return $this;
    }

    private function checkHandlerInstance(array $handlers): void
    {
        foreach ($handlers as $handlerKey => $handler) {
            if (!$handler instanceof HandlerInterface) {
                throw new \InvalidArgumentException(
                    'The handler instance "' . $handlerKey . '" must implement '
                    . HandlerInterface::class
                );
            }
        }
    }
}
