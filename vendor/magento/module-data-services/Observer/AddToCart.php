<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DataServices\Observer;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\DataServices\Model\ProductContextInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\Serializer\Base64Json;
use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\Cookie\PublicCookieMetadata;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * AddToCart observer for data services events
 */
class AddToCart implements ObserverInterface
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
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Base64Json
     */
    private $base64JsonSerializer;

    /**
     * @var ProductContextInterface
     */
    private $productContext;

    /**
     * @var ConfigInterface
     */
    private $sessionConfig;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param CheckoutSession $checkoutSession
     * @param Json $jsonSerializer
     * @param Base64Json $base64JsonSeerializer
     * @param ProductContextInterface $productContext
     * @param ConfigInterface $sessionConfig
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        CheckoutSession $checkoutSession,
        Json $jsonSerializer,
        Base64Json $base64JsonSerializer,
        ProductContextInterface $productContext,
        ConfigInterface  $sessionConfig
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->checkoutSession = $checkoutSession;
        $this->jsonSerializer = $jsonSerializer;
        $this->base64JsonSerializer = $base64JsonSerializer;
        $this->productContext = $productContext;
        $this->sessionConfig = $sessionConfig;
    }

    /**
     * Adds the cart id to a cookie for retrieval for data services js events
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
            "dataservices_cart_id",
            $this->jsonSerializer->serialize($this->checkoutSession->getQuoteId()),
            $publicCookieMetadata
        );

        $productContext = $this->productContext->getContextData($observer->getEvent()->getProduct());
        $productData = $this->base64JsonSerializer->serialize($productContext);
        $this->cookieManager->setPublicCookie(
            "dataservices_product_context",
            $productData,
            $publicCookieMetadata
        );
    }
}
