<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Layout\Config;

use Amasty\Base\Model\Serializer;

class PageTypeRelatedModifier
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var array
     */
    private $additionalBlocksModifiers;

    /**
     * @example for $additionalBlocksModifiers
     * [
     *      'amblog_index_author' => \Amasty\Blog\Model\Layout\Config\DynamicModifierInterface $modifier,
     *      'amblog_index_tags' => [
     *                                'left_side' => ['author_info']
     *                                'content' => ['tags']
     *                             ]
     * ]
     *
     * @param Serializer $serializer
     * @param array $additionalBlocksModifiers
     */
    public function __construct(
        Serializer $serializer,
        array $additionalBlocksModifiers = []
    ) {
        $this->serializer = $serializer;
        $this->additionalBlocksModifiers = $additionalBlocksModifiers;
    }

    public function modify(string $pageIdentifier, string $jsonConfig): string
    {
        if (!empty($this->additionalBlocksModifiers[$pageIdentifier])) {
            $modifier = $this->additionalBlocksModifiers[$pageIdentifier];
            $config = $this->serializer->unserialize($jsonConfig);

            if ($modifier instanceof DynamicModifierInterface) {
                $config = $modifier->modify($config);
            } else {
                $config = array_merge_recursive($modifier, $config);
            }

            $jsonConfig = $this->serializer->serialize($config);
        }

        return $jsonConfig;
    }
}
