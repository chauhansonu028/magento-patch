<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Amasty_BlogPageBuilder
*/


declare(strict_types=1);

namespace Amasty\BlogPageBuilder\Model\Renderer;

use Psr\Log\LoggerInterface;

class Widget implements \Magento\PageBuilder\Model\Stage\RendererInterface
{
    /**
     * @var \Amasty\Blog\Api\PostRepositoryInterface
     */
    private $postRepository;

    /**
     * @var \Amasty\Blog\Model\Di\Wrapper
     */
    private $widgetDirectiveRenderer;

    /**
     * @var LoggerInterface
     */
    private $loggerInterface;

    public function __construct(
        \Amasty\Blog\Api\PostRepositoryInterface $postRepository,
        LoggerInterface $loggerInterface,
        \Amasty\BlogPageBuilder\Model\Renderer\WidgetDirective\Wrapper $widgetDirectiveRenderer
    ) {
        $this->postRepository = $postRepository;
        $this->widgetDirectiveRenderer = $widgetDirectiveRenderer;
        $this->loggerInterface = $loggerInterface;
    }

    /**
     * Render a state object for the specified block for the stage preview
     *
     * @param array $params
     * @return array
     */
    public function render(array $params): array
    {
        $result = [
            'content' => null
        ];

        if (!isset($params['instance_id']) && empty($params['instance_id'])) {
            return $result;
        }

        $directiveResult = $this->widgetDirectiveRenderer->render($params);
        $result['content'] = $directiveResult['content'];

        return $result;
    }
}
