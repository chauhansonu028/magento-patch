<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaypalOnBoarding\Block\Adminhtml\System\Config;

use Laminas\Http\Response;
use Magento\PaypalOnBoarding\Model\Button\RequestCommand;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\HTTP\LaminasClient;
use Magento\Framework\HTTP\LaminasClientFactory;
use Magento\PaypalOnBoarding\Model\MiddlemanService;
use PHPUnit\Framework\TestCase;

/**
 * Class contains tests for PayPal On-Boarding integration
 *
 * @magentoAppArea adminhtml
 */
class OnBoardingWizardTest extends TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var LaminasClientFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $clientFactory;

    /**
     * @var OnBoardingWizard
     */
    private $onBoardingWizard;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->clientFactory = $this->getMockBuilder(LaminasClientFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $middlemanService = $this->objectManager->create(MiddlemanService::class, [
            'requestButtonCommand' => $this->objectManager->create(RequestCommand::class, [
                'clientFactory' => $this->clientFactory
            ])
        ]);

        $this->onBoardingWizard = $this->objectManager->create(OnBoardingWizard::class, [
            'middlemanService' => $middlemanService
        ]);
    }

    /**
     * Check if OnBoardingWizard buttons contains links to PayPal
     */
    public function testOnBoardingWizardButton()
    {
        $liveButtonUrl = "https://www.paypal.com/webapps/merchantboarding/webflow/externalpartnerflow";
        $sandboxButtonUrl = "https://www.sandbox.paypal.com/webapps/merchantboarding/webflow/externalpartnerflow";
        $middlemanResponse = json_encode(['liveButtonUrl' => $liveButtonUrl, 'sandboxButtonUrl' => $sandboxButtonUrl]);

        /** @var LaminasClient|\PHPUnit_Framework_MockObject_MockObject $httpClient */
        $httpClient = $this->getMockBuilder(LaminasClient::class)
            ->disableOriginalConstructor()
            ->setMethods(['setUri', 'setParameterGet', 'send'])
            ->getMock();

        $this->clientFactory->expects(static::once())
            ->method('create')
            ->willReturn($httpClient);

        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->setMethods(['getBody', 'getStatusCode'])
            ->getMock();

        $response->expects(static::exactly(2))
            ->method('getBody')
            ->willReturn($middlemanResponse);
        $response->expects(static::once())
            ->method('getStatusCode')
            ->willReturn(200);

        $httpClient->expects(static::once())
            ->method('send')
            ->willReturn($response);

        $html = $this->onBoardingWizard->toHtml();

        $this->assertContains($liveButtonUrl, $html);
        $this->assertContains($sandboxButtonUrl, $html);
    }
}
