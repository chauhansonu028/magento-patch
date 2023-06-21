<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Plugin\Amp\Block\Page\Html\Header;

use Amasty\Amp\Block\Page\Html\Header\Topmenu;

class AddBlogInAmpHeaderPlugin
{
    /**
     * @var \Amasty\Blog\Plugin\Block\Topmenu
     */
    private $amMenuPlugin;

    public function __construct(
        \Amasty\Blog\Plugin\Block\Topmenu $amMenuPlugin
    ) {
        $this->amMenuPlugin = $amMenuPlugin;
    }

    /**
     * @param Topmenu $subject
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @see Topmenu
     */
    public function beforeGetMenuHtml(
        Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = ''
    ): void {
        $this->amMenuPlugin->beforeGetHtml($subject, $outermostClass, $childrenWrapClass);
    }
}
