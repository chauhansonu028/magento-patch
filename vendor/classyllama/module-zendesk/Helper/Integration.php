<?php

namespace Zendesk\Zendesk\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Integration\Api\IntegrationServiceInterface;
use Magento\Integration\Api\OauthServiceInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Integration extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const INTEGRATION_NAME = 'Zendesk';

    public const INTEGRATION_RESOURCES = [
        'Zendesk_Zendesk::zendesk',
        'Magento_Customer::customer',
        'Magento_Sales::sales',
        'Magento_Sales::actions_view'
    ];

    /**
     * @var IntegrationServiceInterface
     */
    protected $integrationService;

    /**
     * @var \Magento\Integration\Model\Integration|null
     */
    protected $integration;

    /**
     * @var OauthServiceInterface
     */
    protected $oauthService;

    /**
     * @var ScopeHelper
     */
    protected $scopeHelper;

    /**
     * Integration constructor.
     *
     * @param Context $context
     * @param IntegrationServiceInterface $integrationService
     * @param OauthServiceInterface $oauthService
     * @param ScopeHelper $scopeHelper
     */
    public function __construct(
        Context $context,
        // End parent parameters
        IntegrationServiceInterface $integrationService,
        OauthServiceInterface $oauthService,
        ScopeHelper $scopeHelper
    ) {
        parent::__construct($context);
        $this->integrationService = $integrationService;
        $this->oauthService = $oauthService;
        $this->scopeHelper = $scopeHelper;
    }

    /**
     * Get integration name
     *
     * @param string $scopeType
     * @param string|null $scopeId
     * @return string
     */
    public function getIntegrationName(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = null
    ) {
        return self::INTEGRATION_NAME . ($scopeId === null ? '' : '_' . $scopeType . '_' . $scopeId);
    }

    /**
     * Get Zendesk integration instance
     *
     * @param string $scopeType
     * @param string|null $scopeId
     * @return \Magento\Integration\Model\Integration|mixed|null
     * @throws NoSuchEntityException
     */
    public function getIntegration(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = null
    ) {
        $integrationCode = $this->getIntegrationName($scopeType, $scopeId);
        if (empty($this->integration[$integrationCode])) {
            $integration = $this->integrationService->findByName($integrationCode);

            if (empty($integration->getId())) {
                throw new NoSuchEntityException(__('Zendesk integration in Magento is not configured.'));
            }

            $this->integration[$integrationCode] = $integration;
        }
        return $this->integration[$integrationCode];
    }

    /**
     * Get Zendesk integration auth token
     *
     * @param string $scopeType
     * @param string|null $scopeId
     * @return string
     * @throws IntegrationException
     * @throws NoSuchEntityException
     */
    public function getAuthToken(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = null
    ) {
        $integration = $this->getIntegration($scopeType, $scopeId);

        $token = $this->oauthService->getAccessToken($integration->getConsumerId());

        if (!$token) {
            throw new IntegrationException(__('Unable to get Zendesk integration auth token.'));
        }

        return $token->getToken();
    }

    /**
     * Get Zendesk integration data array
     *
     * @param string $scopeType
     * @param string|null $scopeId
     * @return array
     */
    public function getIntegrationData(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = null
    ) {
        return [
            'name' => $this->getIntegrationName($scopeType, $scopeId),
            'status' => \Magento\Integration\Model\Integration::STATUS_INACTIVE,
            'all_resources' => false,
            'resource' => self::INTEGRATION_RESOURCES
        ];
    }

    /**
     * Create integration if it doesn't already exist.
     *
     * @param string $scopeType
     * @param string|null $scopeId
     * @return void
     * @throws IntegrationException
     */
    public function createIntegration(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = null
    ) {
        try {
            $integration = $this->getIntegration($scopeType, $scopeId);

            return; // Integration already exists -- nothing to do.
        } catch (NoSuchEntityException $e) {
            // Intentionally swallow exception and allow process to continue.
            $integration = null;
        }

        $integration = $this->integrationService->create($this->getIntegrationData($scopeType, $scopeId));

        if ($this->oauthService->createAccessToken($integration->getConsumerId())) {
            $integration->setStatus(\Magento\Integration\Model\Integration::STATUS_ACTIVE)->save();
        }
    }

    /**
     * Remove integration, if it exists.
     *
     * @param string $scopeType
     * @param string|null $scopeId
     * @return void
     * @throws IntegrationException
     */
    public function removeIntegration(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeId = null
    ) {
        try {
            $integration = $this->getIntegration($scopeType, $scopeId);

            $this->integrationService->delete($integration->getId());
        } catch (NoSuchEntityException $e) {
            return; // Integration doesn't exist -- nothing to do here.
        }
    }
}
