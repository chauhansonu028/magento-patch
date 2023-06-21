<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Category\DataProvider\Modifiers;

use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\File\FileScope\FileScopeDataProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;

class Data
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var FileScopeDataProviderInterface
     */
    private $fileScopeDataProvider;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Registry $registry,
        RequestInterface $request,
        FileScopeDataProviderInterface $fileScopeDataProvider
    ) {
        $this->registry = $registry;
        $this->fileScopeDataProvider = $fileScopeDataProvider;
        $this->request = $request;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function execute(array $data)
    {
        $category = $this->registry->registry('category');

        if ($category && $category->getId()) {
            $data[$category->getId()]['attachments']['files'] = $this->fileScopeDataProvider->execute(
                [
                    RegistryConstants::STORE => $this->request->getParam('store', 0),
                    RegistryConstants::CATEGORY => $category->getId()
                ],
                'category'
            );
        }

        return $data;
    }
}
