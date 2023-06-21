<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\DataServices\Observer;

use Magento\DataServices\Model\ProductContextInterface;
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

/**
 * RemoveFromCart observer for data services events
 */
class RemoveFromCart implements ObserverInterface
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
     * @var Json
     */
    private $jsonSerializer;

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
     * @param Json $jsonSerializer
     * @param ProductContextInterface $productContext
     * @param ConfigInterface $sessionConfig
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Json $jsonSerializer,
        ProductContextInterface $productContext,
        ConfigInterface  $sessionConfig
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->jsonSerializer = $jsonSerializer;
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

        $productContext = $this->productContext->getContextData($observer->getEvent()->getQuoteItem()->getProduct());
        $productData = $this->jsonSerializer->serialize($productContext);
        $this->cookieManager->setPublicCookie(
            "dataservices_product_context",
            $productData,
            $publicCookieMetadata
        );
    }
}
