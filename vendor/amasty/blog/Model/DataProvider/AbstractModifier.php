<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\DataProvider;

use Magento\Framework\DataObject;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class AbstractModifier implements ModifierInterface
{
    public const CURRENT_STORE_ID = 'amasty_blog_store_id';

    /**
     * @var \Amasty\Blog\Model\BlogRegistry
     */
    private $blogRegistry;

    /**
     * @var string
     */
    private $currentEntityKey;

    /**
     * @var array
     */
    private $fieldsByStore;

    /**
     * @var mixed
     */
    private $repository;

    public function __construct(
        \Amasty\Blog\Model\BlogRegistry $blogRegistry,
        $currentEntityKey = '',
        $fieldsByStore = [],
        array $data = []
    ) {
        $this->blogRegistry = $blogRegistry;
        $this->currentEntityKey = $currentEntityKey;
        $this->fieldsByStore = $fieldsByStore;
        $this->repository = $data['repository'];
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $storeId = (int)$this->blogRegistry->registry(self::CURRENT_STORE_ID);
        if ($storeId) {
            $item = $this->blogRegistry->registry($this->currentEntityKey);
            if ($item) {
                $itemId = $item->getId();
                $this->changeFields($itemId, $storeId, $meta);
            }
        }

        return $meta;
    }

    /**
     * @param int $itemId
     * @param int $storeId
     * @param array $meta
     */
    private function changeFields($itemId, $storeId, &$meta)
    {
        $item = $this->repository->getByIdAndStore($itemId, (int)$storeId, false);
        $this->enrichmentFields($this->fieldsByStore, $item);
        $meta = $this->fieldsByStore;
    }

    private function enrichmentFields(array &$fields, DataObject $item): void
    {
        foreach ($fields as $key => &$field) {
            if (is_array($field) && !isset($field['is_new'])) {
                $this->enrichmentFields($field, $item);
            } elseif (is_string($field)) {
                $fields['children'][$field]['arguments']['data']['config'] = [
                    'service' => [
                        'template' => 'ui/form/element/helper/service',
                    ],
                    'disabled' => $item->getData($field) === null
                ];
                $fields['children'][$field]['is_new'] = true;
                unset($fields[$key]);
            }
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
