<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Test\Unit\Model\Blog;

use Amasty\Blog\Model\Blog\MetaDataResolver;
use Amasty\Blog\Model\ConfigProvider;
use Amasty\Blog\Test\Unit\Traits;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\View\Result\Page as ResultPage;

/**
 * @see MetaDataResolver
 */
class MetaDataResolverTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var MetaDataResolver
     */
    private $model;

    protected function setUp(): void
    {
        $this->model = $this->getObjectManager()->getObject(MetaDataResolver::class);
        $string = $this->getObjectManager()->getObject(StringUtils::class);

        $this->setProperty($this->model, 'string', $string, MetaDataResolver::class);
    }

    /**
     * @param string $description
     * @param string $result
     *
     * @covers MetaDataResolver::cutDescription
     * @dataProvider getCutDescriptionProvider
     */
    public function testCutDescription(string $description, string $result)
    {
        $this->assertEquals($result, $this->model->cutDescription($description));
    }

    /**
     * @param string $metaTitle
     * @param string $prefix
     * @param string $postfix
     *
     * @covers MetaDataResolver::preparePageMetadata
     * @dataProvider getPageMetadataProvider
     *
     * @throws \ReflectionException
     */
    public function testPreparePageMetadata(string $metaTitle, string $prefix, string $postfix)
    {
        $config = $this->createPartialMock(ConfigProvider::class, ['getTitlePrefix', 'getTitleSuffix']);
        $config->method('getTitlePrefix')->willReturn($prefix);
        $config->method('getTitleSuffix')->willReturn($postfix);
        $this->setProperty($this->model, 'configProvider', $config, MetaDataResolver::class);
        $pageConfig = $this->getObjectManager()->getObject(\Magento\Framework\View\Page\Config::class);
        $escaper = $this->getObjectManager()->getObject(\Magento\Framework\Escaper::class);
        $this->setProperty($pageConfig, 'escaper', $escaper, \Magento\Framework\View\Page\Config::class);

        $layout = $this->getObjectManager()->getObject(\Magento\Framework\View\Layout::class);
        $page = $this->getMockBuilder(ResultPage::class)
            ->disableOriginalConstructor()
            ->setMethods(['getConfig', 'getLayout'])
            ->getMock();
        $page->expects($this->any())->method('getConfig')->willReturn($pageConfig);
        $page->expects($this->any())->method('getLayout')->willReturn($layout);

        $this->model->preparePageMetadata($page, 'metatitle', '', '', '', '');
        $this->assertEquals($metaTitle, $page->getConfig()->getMetaTitle());
    }

    /**
     * Data provider for cutDescription test
     * @return array
     */
    public function getCutDescriptionProvider(): array
    {
        // phpcs:disable
        return [
            ['<b>test</b>', 'test'],
            ['this is text without changes', 'this is text without changes'],
            [
                'KMovihQt7GsauymfhGUK7Gfl70Dydu8FsrEq1IRKugL8VPvSHevviuSyWyyqDsbtz9a5WBW48LzkzhmtsYZdfV3tSfgt3sRZRgtXLrNfvRE5tVpqRy9bSLNRNC5fiPwbvz6Mn6ilbZfxKZhg9OtEeiPlREvHarDWlVkQj2PLtmj8S9koju5gBcOLHM5PJwuNtzTMSMGmTDQgKLqrcZGnk3H60Xe0YSgURb0ZbMGsWmWaltW04bFGnVxKadWPIxI1',
                'KMovihQt7GsauymfhGUK7Gfl70Dydu8FsrEq1IRKugL8VPvSHevviuSyWyyqDsbtz9a5WBW48LzkzhmtsYZdfV3tSfgt3sRZRgtXLrNfvRE5tVpqRy9bSLNRNC5fiPwbvz6Mn6ilbZfxKZhg9OtEeiPlREvHarDWlVkQj2PLtmj8S9koju5gBcOLHM5PJwuNtzTMSMGmTDQgKLqrcZGnk3H60Xe0YSgURb0ZbMGsWmWaltW04bFGnVxKadWPIxI'
            ],
        ];
        // phpcs:enable
    }

    /**
     * Data provider for testPreparePageMetadata test
     * @return array
     */
    public function getPageMetadataProvider(): array
    {
        return [
            ['TitlePrefix - metatitle | TitleSuffix', 'TitlePrefix', 'TitleSuffix'],
            ['metatitle', '', ''],
            ['TitlePrefix - metatitle', 'TitlePrefix', ''],
            ['metatitle | TitleSuffix', '', 'TitleSuffix'],
        ];
    }
}
