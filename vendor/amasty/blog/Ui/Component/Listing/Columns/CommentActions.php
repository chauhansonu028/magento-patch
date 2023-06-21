<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

class CommentActions extends Column
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
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $commentName = $this->escaper->escapeHtml($item['name']);
                $item[$name]['approve'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_blog/comments/approve',
                        ['id' => $item['comment_id']]
                    ),
                    'label' => __('Approve')
                ];
                $item[$name]['reject'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_blog/comments/reject',
                        ['id' => $item['comment_id']]
                    ),
                    'label' => __('Reject')
                ];
                $item[$name]['reply'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_blog/comments/reply',
                        ['id' => $item['comment_id']]
                    ),
                    'label' => __('Reply')
                ];
                $item[$name]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_blog/comments/edit',
                        ['id' => $item['comment_id']]
                    ),
                    'label' => __('Edit')
                ];
                $item[$name]['delete'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'amasty_blog/comments/delete',
                        ['id' => $item['comment_id']]
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete %1', $commentName),
                        'message' => __('Are you sure you wan\'t to delete a %1 record?', $commentName),
                        '__disableTmpl' => false
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
