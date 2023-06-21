<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content\Post;

use Amasty\Blog\Helper\Date;
use Amasty\Blog\Model\ConfigProvider;
use Amasty\Blog\Model\Posts;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @method getViewModel()
 */
class RelatedProducts extends Template implements IdentityInterface
{
    /**
     * @var Date
     */
    private $helperDate;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Context $context,
        Date $helperDate,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperDate = $helperDate;
        $this->configProvider = $configProvider;
    }

    public function getIdentities()
    {
        /** @var \Amasty\Blog\ViewModel\Posts\RelatedProducts $viewModel **/
        $viewModel = $this->getViewModel();
        $currentPost = $viewModel->getCurrentPost();
        $identities = [Posts::CACHE_TAG . '_' . $currentPost->getPostId()];

        return array_reduce($viewModel->getPostProducts(), function (array $carry, Product $product): array {
            return array_merge($carry, $product->getIdentities());
        }, $identities);
    }

    public function renderDate(string $datetime, bool $isEditedAt = false): string
    {
        return $this->helperDate->renderDate($datetime, false, false, $isEditedAt);
    }

    public function isHumanized(): bool
    {
        return $this->configProvider->getDateFormat() === Date::DATE_TIME_PASSED;
    }

    public function isShowEditedAt(): bool
    {
        return $this->configProvider->isShowEditedAt();
    }

    public function isHumanizedEditedAt(): bool
    {
        return $this->configProvider->getEditedAtDateFormat() === Date::DATE_TIME_PASSED;
    }
}
