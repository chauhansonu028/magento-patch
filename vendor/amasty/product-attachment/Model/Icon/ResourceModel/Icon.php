<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Icon\ResourceModel;

use Amasty\ProductAttachment\Api\Data\IconInterface;
use Amasty\ProductAttachment\Model\Icon\OptionSource\Status;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Icon extends AbstractDb
{
    public const TABLE_NAME = 'amasty_file_icon';
    public const ICON_EXTENSION_TABLE_NAME = 'amasty_file_icon_extension';

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, IconInterface::ICON_ID);
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel|IconInterface $object
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->saveExtensions($object);
        parent::_afterSave($object);
    }

    /**
     * @param \Magento\Framework\DataObject|IconInterface $icon
     */
    public function saveExtensions($icon)
    {
        if ($icon->getIconId()) {
            $this->getConnection()->delete(
                $this->getTable(self::ICON_EXTENSION_TABLE_NAME),
                [IconInterface::ICON_ID . ' = ?' => $icon->getIconId()]
            );
        }

        if (($extensions = $icon->getExtension()) && is_array($extensions)) {
            foreach ($extensions as $extension) {
                $bind = [IconInterface::ICON_ID => $icon->getIconId(), IconInterface::EXTENSION => $extension];
                $this->getConnection()->insert(
                    $this->getTable(self::ICON_EXTENSION_TABLE_NAME),
                    $bind
                );
            }
        }
    }

    /**
     * @param int $iconId
     *
     * @return array
     */
    public function getIconExtensions($iconId)
    {
        $select = $this->getConnection()->select()
            ->from(['ie' => $this->getTable(self::ICON_EXTENSION_TABLE_NAME)])
            ->where('ie.' . IconInterface::ICON_ID . ' = ?', (int)$iconId)
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('ie.' . IconInterface::EXTENSION);

        if ($result = $this->getConnection()->fetchAll($select)) {
            array_walk($result, function (&$item) {
                $item = $item[IconInterface::EXTENSION];
            });
            return $result;
        }

        return [];
    }

    /**
     * @return array
     */
    public function getAllowedExtensions()
    {
        $select = $this->getConnection()->select()
            ->from(
                ['ie' => $this->getTable(self::ICON_EXTENSION_TABLE_NAME)],
                [IconInterface::EXTENSION]
            )->joinInner(
                ['icon' => $this->getMainTable()],
                'ie.' . IconInterface::ICON_ID . ' = ' . 'icon.' . IconInterface::ICON_ID
            )->where('icon.' . IconInterface::IS_ACTIVE . ' = ?', Status::ENABLED);

        if ($result = $this->getConnection()->fetchAll($select)) {
            $allowExtensions = [];
            foreach ($result as $extension) {
                $allowExtensions[] = $extension[IconInterface::EXTENSION];
            }

            return $allowExtensions;
        }

        return [];
    }

    /**
     * @param string $extension
     *
     * @return string
     */
    public function getExtensionIconImage($extension)
    {
        $select = $this->getConnection()->select()
            ->from(['ie' => $this->getTable(self::ICON_EXTENSION_TABLE_NAME)])
            ->joinInner(
                ['icon' => $this->getTable(Icon::TABLE_NAME)],
                'ie.' . IconInterface::ICON_ID . ' = ' . 'icon.' . IconInterface::ICON_ID
            )->where('icon.' . IconInterface::IS_ACTIVE . ' = ?', Status::ENABLED)
            ->where('ie.' . IconInterface::EXTENSION . ' = ?', $extension)
            ->reset(\Magento\Framework\DB\Select::COLUMNS)
            ->columns('icon.' . IconInterface::IMAGE);

        return $this->getConnection()->fetchOne($select) ?: '';
    }
}
