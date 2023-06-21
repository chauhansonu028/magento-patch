<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaypalOnBoarding\Model\Button;

use Laminas\Http\Exception\RuntimeException;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Framework\HTTP\LaminasClient;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\ValidatorException;

/**
 * Place request for getting urls from Middleman application
 */
class RequestCommand
{
    /**
     * @var LaminasClientFactory
     */
    private $clientFactory;

    /**
     * @var ResponseValidator
     */
    private $responseButtonValidator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LaminasClientFactory $clientFactory
     * @param ResponseValidator $responseButtonValidator
     * @param LoggerInterface $logger
     */
    public function __construct(
        LaminasClientFactory $clientFactory,
        ResponseValidator $responseButtonValidator,
        LoggerInterface $logger
    ) {
        $this->clientFactory = $clientFactory;
        $this->responseButtonValidator = $responseButtonValidator;
        $this->logger = $logger;
    }

    /**
     * Place http request
     *
     * @param string $host
     * @param array $requestParams
     * @param array $responseFields fields should be present in response
     * @return string
     * @throws ValidatorException
     * @throws RuntimeException
     */
    public function execute($host, array $requestParams, array $responseFields)
    {
        /** @var LaminasClient $client */
        $client = $this->clientFactory->create();
        $client->setParameterGet($requestParams);
        $client->setUri($host);

        $result = '';
        try {
            $response = $client->send();
            $this->responseButtonValidator->validate(
                $response,
                $responseFields
            );
            $result = $response->getBody();
        } catch (ValidatorException $e) {
            $this->logger->error($e->getMessage());
        } catch (RuntimeException $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }
}
