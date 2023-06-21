<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DataServices\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * LogOut observer for data services events
 */
class LogOut implements ObserverInterface
{
    /**
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var ConfigInterface
     */
    protected $sessionConfig;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param ConfigInterface $sessionConfig
     * @param Json $jsonSerializer
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        ConfigInterface  $sessionConfig,
        Json $jsonSerializer
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionConfig = $sessionConfig;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * Set a flag if customers logs in or out
     *
     * @param Observer $observer
     * @return void
     * @throws FailureToSendException If cookie couldn't be sent to the browser.
     * @throws CookieSizeLimitReachedException Thrown when the cookie is too big to store any additional data.
     * @throws InputException If the cookie name is empty or contains invalid characters.
     * @throws NoSuchEntityException If store entity cannot be found
     */
    public function execute(Observer $observer)
    {
        /** @var PublicCookieMetadata $publicCookieMetadata */
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($this->sessionConfig->getCookieLifetime())
            ->setPath('/')
            ->setDomain($this->sessionConfig->getCookieDomain())
            ->setHttpOnly(false);

        $this->cookieManager->setPublicCookie(
            "authentication_flag",
            $this->jsonSerializer->serialize(true),
            $publicCookieMetadata
        );

        $this->cookieManager->deleteCookie("dataservices_customer_id", $publicCookieMetadata);
        $this->cookieManager->deleteCookie("dataservices_customer_group", $publicCookieMetadata);
        $this->cookieManager->deleteCookie("dataservices_cart_id", $publicCookieMetadata);
    }
}
