<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Config\Backend;

use Amasty\Base\Model\Serializer;
use Amasty\Blog\Model\Layout\CacheableGenerator;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry as CoreRegistry;

class LayoutConfig extends Value
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var FrontendInterface
     */
    private $cache;

    public function __construct(
        Context $context,
        CoreRegistry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Serializer $serializer,
        FrontendInterface $cache,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->cache = $cache;

        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    public function beforeSave(): LayoutConfig
    {
        if ($this->isValueChanged()) {
            $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, [CacheableGenerator::CACHE_TAG]);
        }

        return parent::beforeSave();
    }

    public function isValueChanged()
    {
        $newValue = $this->serializer->unserialize($this->getValue());
        $oldValue = $this->serializer->unserialize($this->getOldValue());

        return !$this->isArrayEqualsRecursive($newValue, $oldValue);
    }

    private function isArrayEqualsRecursive(array $arrayFirst, array $arraySecond): bool
    {
        $result = count($arrayFirst) === count($arraySecond);

        if ($result) {
            foreach ($arrayFirst as $key => $value) {
                if (!array_key_exists($key, $arraySecond)) {
                    $result = false;
                    break;
                }

                if (!is_array($value) && $value !== $arraySecond[$key]) {
                    $result = false;
                    break;
                }

                if (is_array($value) && !$this->isArrayEqualsRecursive($value, $arraySecond[$key])) {
                    $result = false;
                    break;
                }
            }
        }

        return $result;
    }
}
