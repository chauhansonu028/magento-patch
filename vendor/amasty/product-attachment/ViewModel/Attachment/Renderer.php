<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\ViewModel\Attachment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Layout;

class Renderer
{
    /**
     * @var Layout
     */
    private $layout;

    /**
     * @var Template
     */
    private $block;

    /**
     * @var array
     */
    private $config = [
        'wrapper' => [
            'template' => 'Amasty_ProductAttachment::attachments/file/wrapper.phtml',
            'processors' => []
        ],
        'item' => [
            'template' => 'Amasty_ProductAttachment::attachments/file.phtml',
            'processors' => []
        ]
    ];

    public function __construct(
        Layout $layout,
        Template $block,
        array $config = []
    ) {
        $this->layout = $layout;
        $this->block = $block;
        $this->config = array_merge($this->config, $config);
    }

    public function getFilesHtml(array $files): string
    {
        try {
            $data = [
                'files' => $files,
                'parent_name' => $this->block->getNameInLayout()
            ];
            foreach ($this->config['wrapper']['processors'] as $processor) {
                $data = $processor->process($data);
            }
            $block = $this->layout->createBlock(
                Template::class,
                '',
                ['data' => $data]
            )->setTemplate($this->config['wrapper']['template']);
            $html = $block->toHtml();
        } catch (LocalizedException $e) {
            $html = '';
        }

        return $html;
    }

    public function getFileItemHtml($file): string
    {
        try {
            $data = [
                'file' => $file,
                'is_show_icon' => $this->block->isShowIcon(),
                'is_show_filesize' => $this->block->isShowFilesize()
            ];
            foreach ($this->config['wrapper']['processors'] as $processor) {
                $data = $processor->process($data);
            }
            $block = $this->layout->createBlock(
                Template::class,
                '',
                ['data' => $data]
            )->setTemplate($this->config['item']['template']);
            $html = $block->toHtml();
        } catch (LocalizedException $e) {
            $html = '';
        }

        return $html;
    }
}
