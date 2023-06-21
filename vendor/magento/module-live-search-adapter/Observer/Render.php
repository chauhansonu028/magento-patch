<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchAdapter\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;
use Magento\LiveSearchAdapter\Model\SearchAdapter as LiveSearchAdapter;

/**
 * Adds Search results headers after HTTP response is generated.
 */
class Render implements ObserverInterface
{
    /**
     * @var LiveSearchAdapter
     */
    private LiveSearchAdapter $searchAdapter;

    /**
     * @var array
     */
    private array $emptyResultResponseHeaders;

    /**
     * @param LiveSearchAdapter $searchAdapter
     * @param array $emptyResultResponseHeaders
     */
    public function __construct(
        LiveSearchAdapter $searchAdapter,
        array $emptyResultResponseHeaders = []
    ) {
        $this->searchAdapter = $searchAdapter;
        $this->emptyResultResponseHeaders = $emptyResultResponseHeaders;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if (empty($this->emptyResultResponseHeaders)) {
            return;
        }

        // set specific response headers to prevent caching search results caused by Search Service exceptions.
        if (null !== $this->searchAdapter->getSearchResults()
            && 0 === $this->searchAdapter->getSearchResults()->getTotalCount()
            && null !== $this->searchAdapter->getSearchException()
        ) {
            /** @var HttpResponse $response */
            $response = $observer->getEvent()->getData('response');
            foreach ($this->emptyResultResponseHeaders as $headerName => $headerValue) {
                $response->setHeader($headerName, $headerValue, true);
            }
        }
    }
}
