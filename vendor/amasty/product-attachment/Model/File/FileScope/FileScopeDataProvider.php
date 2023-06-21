<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\File\FileScope;

class FileScopeDataProvider implements FileScopeDataProviderInterface
{
    /**
     * @var DataProviders\FileScopeDataInterface[]
     */
    private $dataProviders;

    public function __construct(
        $dataProviders
    ) {
        $this->dataProviders = $dataProviders;
    }

    /**
     * @inheritdoc
     */
    public function execute($params, $dataProviderName)
    {
        if (!isset($this->dataProviders[$dataProviderName])) {
            throw new \Amasty\ProductAttachment\Exceptions\NoSuchDataProviderException();
        }

        return $this->dataProviders[$dataProviderName]->execute($params);
    }
}
