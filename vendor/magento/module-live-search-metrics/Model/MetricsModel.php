<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LiveSearchMetrics\Model;

use Magento\Framework\Serialize\Serializer\Json;

class MetricsModel
{
    /**
     * @var string|null
     */
    private ?string $graphqlRequest = null;

    /**
     * @var string|null
     */
    private ?string $graphqlResponse = null;

    /**
     * @var int|null
     */
    private ?int $startTime = null;

    /**
     * @var int|null
     */
    private ?int $endTime = null;

    /**
     * @var Json
     */
    private Json $serializer;

    /**
     * @param Json $serializer
     */
    public function __construct(Json $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Save page load start time to local variable.
     *
     * @return void
     */
    public function saveStartTime(): void
    {
        $this->startTime = hrtime(true);
    }

    /**
     * Get start time of page load.
     *
     * @return int|null
     */
    public function getStartTime(): ?int
    {
        return $this->startTime;
    }

    /**
     * Save page load end time to local variable.
     *
     * @return void
     */
    public function saveEndTime(): void
    {
        $this->endTime = hrtime(true);
    }

    /**
     * Save page end start time.
     *
     * @return int|null
     */
    public function getEndTime(): ?int
    {
        return $this->endTime;
    }

    /**
     * Save request body to local variable.
     *
     * @param string $body
     * @return void
     */
    public function saveRequest(string $body): void
    {
        $this->graphqlRequest = $body;
    }

    /**
     * Get request body.
     *
     * @return string|null
     */
    public function getRequest(): ?string
    {
        return $this->graphqlRequest;
    }

    /**
     * Save response to local variable.
     *
     * @param array $unserializedJSON
     *
     * @return void
     */
    public function saveResponse(array $unserializedJSON): void
    {
        $this->graphqlResponse = $this->serializer->serialize($unserializedJSON);
    }

    /**
     * Returns stored response.
     *
     * @return string|null
     */
    public function getResponse(): ?string
    {
        return $this->graphqlResponse;
    }
}
