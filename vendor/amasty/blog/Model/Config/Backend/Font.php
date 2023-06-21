<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Config\Backend;

use Amasty\Blog\Model\FontManager;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Font extends Value implements ProcessorInterface
{
    /**
     * @var FontManager
     */
    private $fontManager;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        FontManager $fontManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->fontManager = $fontManager;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    public function beforeSave(): void
    {
        $value = (string)$this->getValue();
        if (!$this->fontManager->validateFont($value)) {
            $fieldLabel = $this->getData('field_config')['label'] ?? '';
            throw new LocalizedException(__('%1 value is invalid.', $fieldLabel));
        }

        $this->fontManager->addConfigFontStyle($value, $this->getScope(), (int)$this->getScopeId());
    }

    /**
     * @param string $value
     * @return string
     */
    public function processValue($value): string
    {
        return $value;
    }
}
