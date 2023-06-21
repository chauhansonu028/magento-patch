<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Blog Pro for Magento 2
 */

namespace Amasty\Blog\Model\Detection;

use Magento\Framework\HTTP\Header;
use Magento\Framework\ObjectManagerInterface;

class MobileDetection
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Detection\MobileDetect|null
     */
    private $mobileDetector = null;

    /**
     * @var Header
     */
    private $header;

    public function __construct(
        Header $header,
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;

        // We are using object manager to create 3rd-party packages' class
        if (class_exists(\Detection\MobileDetect::class)) {
            $this->mobileDetector = $this->objectManager->create(\Detection\MobileDetect::class);
        }
        $this->header = $header;
    }

    public function isMobile(): bool
    {
        return $this->mobileDetector === null
            ? stristr($this->header->getHttpUserAgent(), 'mobi') !== false
            : $this->mobileDetector->isMobile();
    }
}
