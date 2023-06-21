<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Model\Category;

use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\File\FileScope\SaveFileScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Save implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var SaveFileScopeInterface
     */
    private $saveFileScope;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        SaveFileScopeInterface $saveFileScope,
        StoreManagerInterface $storeManager
    ) {
        $this->saveFileScope = $saveFileScope;
        $this->storeManager = $storeManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $files = $observer->getCategory()->getData('attachments');
        $params = [
            RegistryConstants::FILES => !empty($files['files']) ? $files['files'] : false,
            RegistryConstants::CATEGORY => $observer->getCategory()->getId(),
            RegistryConstants::STORE => (int)$this->storeManager->getStore()->getId()
        ];

        if (!empty($files['delete'])) {
            $params[RegistryConstants::TO_DELETE] = $files['delete'];
        }

        $this->saveFileScope->execute(
            $params,
            'category'
        );
    }
}
