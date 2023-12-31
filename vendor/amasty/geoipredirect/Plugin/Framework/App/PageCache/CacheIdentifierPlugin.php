<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package GeoIP Redirect for Magento 2
 */

namespace Amasty\GeoipRedirect\Plugin\Framework\App\PageCache;

use Magento\Framework\App\PageCache\Identifier;
use Magento\Framework\Session\SessionManagerInterface;

class CacheIdentifierPlugin
{
    public const CACHE_PREFIX = '_amredirect_is_need_popup_';

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    public function __construct(
        SessionManagerInterface $sessionManager
    ) {
        $this->sessionManager = $sessionManager;
    }

    /**
     * @param Identifier $identifier
     * @param string $result
     * @return string
     */
    public function afterGetValue(Identifier $identifier, $result) : string
    {
        if ($this->getNeedShow()) {
            $result .= self::CACHE_PREFIX . $this->sessionManager->getAmPopupCountry();
        }

        return $result;
    }

    private function getNeedShow()
    {
        if (!$this->sessionManager->isSessionExists() || $this->sessionManager->getNeedShow() === null) {
            $this->sessionManager->start();
        }

        return $this->sessionManager->getNeedShow();
    }
}
