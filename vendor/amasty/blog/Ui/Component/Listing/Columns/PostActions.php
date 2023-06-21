<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Ui\Component\Listing\Columns;

use Magento\Framework\Escaper;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;

class PostActions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $postName = $this->escaper->escapeHtml($item['title']);
                $item[$name]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_blog/posts/edit',
                        ['id' => $item['post_id']]
                    ),
                    'label' => __('Edit')
                ];

                $item[$name]['preview'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_blog/posts/preview',
                        ['id' => $item['post_id']]
                    ),
                    'target' => '_blank',
                    'label' => __('Preview')
                ];

                $item[$name]['delete'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_blog/posts/duplicate',
                        ['id' => $item['post_id']]
                    ),
                    'label' => __('Duplicate'),
                    'confirm' => [
                        'title' => __('Duplicate "%1"', $postName),
                        'message' => __('Are you sure you want to duplicate a "%1" record?', $postName),
                        '__disableTmpl' => false
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
