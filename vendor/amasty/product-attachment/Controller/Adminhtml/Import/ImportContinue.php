<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Import;

use Amasty\ProductAttachment\Controller\Adminhtml\Import;
use Amasty\ProductAttachment\Model\Import\Repository;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class ImportContinue extends Import
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        Repository $repository,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->repository = $repository;
    }

    public function execute()
    {
        if ($importId = $this->getRequest()->getParam('import_id')) {
            try {
                $import = $this->repository->getById($importId);
                $storeIds = $import->getData(\Amasty\ProductAttachment\Model\Import\Import::STORE_IDS);
                if ($storeIds === null || $storeIds === '') {
                    return $this->resultRedirectFactory->create()->setPath(
                        'amfile/import/store',
                        ['import_id' => $importId]
                    );
                }

                return $this->resultRedirectFactory->create()->setPath(
                    'amfile/import/fileImport',
                    ['import_id' => $importId]
                );
            } catch (\Exception $e) {
                null;
            }
        }

        return $this->resultRedirectFactory->create()->setPath('/*/*');
    }
}
