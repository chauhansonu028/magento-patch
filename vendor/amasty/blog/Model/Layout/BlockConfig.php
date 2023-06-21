<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Layout;

use Magento\Framework\View\Element\Template as MagentoDefaultBlock;

class BlockConfig
{
    const TYPE_CONTAINER = 'container';
    const TYPE_BLOCK = 'block';
    const DEFAULT_ALIAS = 'amblog_widget';

    /**
     * @var string|null
     */
    private $className;

    /**
     * @var string|null
     */
    private $template;

    /**
     * @var string
     */
    private $layoutName;

    /**
     * @var array|null
     */
    private $arguments;

    /**
     * @var BlockNameGeneratorInterface
     */
    private $blockNameGenerator;

    /**
     * @var string|null
     */
    private $containerType;

    /**
     * @var bool
     */
    private $isNeedWrap;

    /**
     * @var string|null
     */
    private $sectionName;

    public function __construct(
        BlockNameGeneratorInterface $blockNameGenerator,
        bool $isNeedWrap = true,
        ?string $className = null,
        ?array $arguments = null,
        ?string $template = null,
        ?string $layoutName = null,
        ?string $containerType = null,
        ?string $sectionName = null
    ) {
        $this->className = $className;
        $this->template = $template;
        $this->layoutName = $layoutName;
        $this->blockNameGenerator = $blockNameGenerator;
        $this->arguments = $arguments;
        $this->containerType = $containerType;
        $this->isNeedWrap = $isNeedWrap;
        $this->sectionName = $sectionName;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        if ($this->className === null && $this->getContainerType() === self::TYPE_BLOCK) {
            $this->className = MagentoDefaultBlock::class;
        }

        return $this->className;
    }

    /**
     * @return string
     */
    public function getTemplate(): ?string
    {
        return $this->template;
    }

    /**
     * @return string
     */
    public function getLayoutName(): ?string
    {
        if ($this->layoutName === null) {
            $this->layoutName = $this->blockNameGenerator->generate($this->getClassName(), $this->getSectionName());
        }

        return $this->layoutName;
    }

    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    public function getContainerType(): ?string
    {
        return $this->containerType ?: self::TYPE_BLOCK;
    }

    public function isNeedWrap(): bool
    {
        return $this->isNeedWrap;
    }

    public function getAlias(): string
    {
        return self::DEFAULT_ALIAS;
    }

    public function getSectionName(): string
    {
        return $this->sectionName ?: '';
    }
}
