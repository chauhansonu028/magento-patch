<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Ui\Component\Listing\StoredMethods\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Actions extends Column
{
    const STORED_METHODS_DELETE_PATH = 'drpay/storedmethods/delete';

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['source_id'])) {
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            self::STORED_METHODS_DELETE_PATH,
                            ['source_id' => $item['source_id']]
                        ),
                        'label' => __('Delete'),
                        'isAjax' => true,
                        'confirm' => [
                            'title' => __('Delete method'),
                            'message' => __('Are you sure you want to delete the method?')
                        ],
                        'post' => true
                    ];
                }
            }
        }

        return $dataSource;
    }
}
