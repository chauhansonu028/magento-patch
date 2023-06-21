<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaypalOnBoarding\Test\Unit\Model\Button;

use Laminas\Http\Exception\RuntimeException;
use Laminas\Http\Response;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\Framework\HTTP\LaminasClient;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\ValidatorException;
use Magento\PaypalOnBoarding\Model\Button\ResponseValidator;
use Magento\PaypalOnBoarding\Model\Button\RequestCommand;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestCommandTest
 */
class RequestCommandTest extends TestCase
{
    /**
     * @var LaminasClientFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clientFactoryMock;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var ResponseValidator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseButtonValidatorMock;

    /**
     * @var RequestCommand
     */
    private $requestCommand;

    /**
     * @var array $requestParams
     */
    private $requestParams = [
        'countryCode' => 'UK',
        'magentoMerchantId' => 'qwe-rty',
        'successUrl' => 'https://magento.loc/paypal_onboarding/redirect/success',
        'failureUrl' => 'https://magento.loc/paypal_onboarding/redirect/failure'
    ];

    /**
     * @var string
     */
    private $host = 'https://middleman.com/start';

    /**
     * @var array $responseFields
     */
    private $responseFields = ['liveButtonUrl', 'sandboxButtonUrl'];

    protected function setUp(): void
    {
        $this->clientFactoryMock = $this->getMockBuilder(LaminasClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->loggerMock = $this->createMock(LoggerInterface::class);
        $this->responseButtonValidatorMock = $this->createMock(ResponseValidator::class);

        $this->requestCommand = new RequestCommand(
            $this->clientFactoryMock,
            $this->responseButtonValidatorMock,
            $this->loggerMock
        );
    }

    /**
     * Test successful request
     *
     * @covers \Magento\PaypalOnBoarding\Model\Button\RequestCommand::execute()
     */
    public function testExecuteSuccess()
    {

        $liveButtonUrl = "https://www.paypal.com/webapps/merchantboarding/webflow/externalpartnerflow";
        $sandboxButtonUrl = "https://www.sandbox.paypal.com/webapps/merchantboarding/webflow/externalpartnerflow";
        $middlemanResponse = json_encode(['liveButtonUrl' => $liveButtonUrl, 'sandboxButtonUrl' => $sandboxButtonUrl]);

        $httpClient = $this->getHttpClientMock();
        $this->clientFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($httpClient);

        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBody'])
            ->getMock();

        $response->expects(static::once())
            ->method('getBody')
            ->willReturn($middlemanResponse);

        $httpClient->expects(static::once())
            ->method('send')
            ->willReturn($response);

        $this->responseButtonValidatorMock->expects(static::once())
            ->method('validate')
            ->with($response, $this->responseFields)
            ->willReturn(true);

        $this->requestCommand->execute($this->host, $this->requestParams, $this->responseFields);
    }

    /**
     * Request fails due to RuntimeException
     */
    public function testExecuteWithZendHttpClientException()
    {
        $httpClient = $this->getHttpClientMock();
        $this->clientFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($httpClient);
        $httpClient->expects(static::once())
            ->method('send')
            ->willThrowException(new RuntimeException());

        $this->responseButtonValidatorMock->expects(static::never())
            ->method('validate');

        $this->loggerMock->expects(static::once())
            ->method('error');

        $this->requestCommand->execute($this->host, $this->requestParams, $this->responseFields);
    }

    /**
     * Request fails due to ValidatorException
     */
    public function testExecuteWithValidatorException()
    {
        $httpClient = $this->getHttpClientMock();
        $this->clientFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($httpClient);

        $response = $this->createMock(Response::class);
        $httpClient->expects(static::once())
            ->method('send')
            ->willReturn($response);

        $this->responseButtonValidatorMock->expects(static::once())
            ->method('validate')
            ->with($response, $this->responseFields)
            ->willThrowException(new ValidatorException(
                __('error')
            ));

        $this->loggerMock->expects(static::once())
            ->method('error');

        $this->requestCommand->execute($this->host, $this->requestParams, $this->responseFields);
    }

    /**
     * Return LaminasClient mock
     *
     * @return LaminasClient|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getHttpClientMock()
    {
        /** @var LaminasClient|\PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->getMockBuilder(LaminasClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['setUri', 'setParameterGet', 'send'])
            ->getMock();

        $httpClient->expects(static::once())
            ->method('setUri')
            ->with($this->host);
        $httpClient->expects(static::once())
            ->method('setParameterGet')
            ->with($this->requestParams);

        return $httpClient;
    }
}
