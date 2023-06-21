<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Block\Content\Product\View;

use Amasty\Blog\Helper\Date;
use Amasty\Blog\Model\ConfigProvider;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * @method getViewModel()
 * @method setTitle(string $title)
 */
class RelatedPostsTab extends Template implements IdentityInterface
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

    protected function _toHtml()
    {
        /** @var \Amasty\Blog\ViewModel\Product\View\RelatedPosts $viewModel **/
        $viewModel = $this->getViewModel();
        $this->setTitle($this->_escaper->escapeHtml($viewModel->getBlockTitle()));

        return $viewModel->isCanRender() ? parent::_toHtml() : '';
    }

    public function getIdentities()
    {
        /** @var \Amasty\Blog\ViewModel\Product\View\RelatedPosts $viewModel * */
        $viewModel = $this->getViewModel();
        $posts = $viewModel->getPostsForCurrentProduct();

        return array_reduce($posts, function (array $carry, IdentityInterface $post): array {
            return array_merge($carry, $post->getIdentities());
        }, []);
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
