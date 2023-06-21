<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Test\Unit\Block\Content\Product\View;

use Amasty\Blog\Block\Content\Product\View\RelatedPostsTab;
use Amasty\Blog\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Blog\Test\Unit\Traits\ReflectionTrait;
use Amasty\Blog\ViewModel\Product\View\RelatedPosts;
use Magento\Framework\DataObject\IdentityInterface;
use PHPUnit\Framework\TestCase;

/**
 *
 * @see \Amasty\Blog\Block\Content\Product\View\RelatedPostsTab
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class RelatedPostsTabTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @covers       \Amasty\Blog\Block\Content\Product\View\RelatedPostsTab::getIdentities
     * @dataProvider getIdentitiesDataProvider
     *
     * @param array $cacheTags
     * @param array $expectedResult
     */
    public function testGetIdentities(array $cacheTags, array $expectedResult): void
    {
        $posts = [];

        foreach ($cacheTags as $cacheTag) {
            $post = $this->getMockForAbstractClass(IdentityInterface::class);
            $post->expects($this->any())->method('getIdentities')->willReturn($cacheTag);
            $posts[] = $post;
        }

        $viewModel = $this->createMock(RelatedPosts::class);
        $viewModel->expects($this->any())->method('getPostsForCurrentProduct')->willReturn($posts);

        $block = $this->getObjectManager()->getObject(
            RelatedPostsTab::class,
            ['data' => ['view_model' => $viewModel]]
        );

        $this->assertEquals($expectedResult, $block->getIdentities());
    }

    public function getIdentitiesDataProvider(): array
    {
        return [
            [
                [
                    [
                        'cache_tag_1',
                        'cache_tag_2'
                    ],
                    [
                        'cache_tag_3'
                    ]
                ],
                [
                    'cache_tag_1',
                    'cache_tag_2',
                    'cache_tag_3'
                ]
            ],
            [
                [],
                []
            ]
        ];
    }
}
