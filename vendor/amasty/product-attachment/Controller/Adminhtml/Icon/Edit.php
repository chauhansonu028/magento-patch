<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Product Attachments Base for Magento 2
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Icon;

use Amasty\ProductAttachment\Controller\Adminhtml\Icon;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\Icon\IconFactory;
use Amasty\ProductAttachment\Model\Icon\Repository;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Registry;

class Edit extends Icon
{
    /**
     * @var IconFactory
     */
    private $iconFactory;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        IconFactory $iconFactory,
        Repository $repository,
        Registry $registry,
        Action\Context $context
    ) {
        parent::__construct($context);
        $this->iconFactory = $iconFactory;
        $this->repository = $repository;
        $this->registry = $registry;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_ProductAttachment::icon');

        if ($iconId = (int) $this->getRequest()->getParam(RegistryConstants::FORM_ICON_ID)) {
            try {
                $this->repository->getById($iconId);
                $resultPage->getConfig()->getTitle()->prepend(__('Edit Icon'));
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This icon no longer exists.'));

                return $this->resultRedirectFactory->create()->setPath('*/*/index');
            }
        } else {
            $resultPage->getConfig()->getTitle()->prepend(__('New Icon'));
        }

        return $resultPage;
    }
}
