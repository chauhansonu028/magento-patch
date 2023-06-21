<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Ui\Component\Listing\Columns\Column;

use Amasty\ProductAttachment\Api\Data\IconInterface;
use Amasty\ProductAttachment\Model\Filesystem\UrlResolver;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var UrlResolver
     */
    private $iconUrl;

    public function __construct(
        UrlResolver $iconUrl,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->iconUrl = $iconUrl;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (!empty($item[IconInterface::IMAGE])) {
                    $item[$fieldName . '_src'] = $this->iconUrl->getIconUrlByName($item[IconInterface::IMAGE]);
                }
            }
        }

        return $dataSource;
    }
}
