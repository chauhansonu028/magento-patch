<?php
/**
 * @category    ClassyLlama
 * @copyright   Copyright (c) 2020 Classy Llama Studios, LLC
 */

namespace Zendesk\Zendesk\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use stdClass;
use Zendesk\API\Exceptions\AuthException;
use Zendesk\API\Exceptions\ApiResponseException;

class Sunshine extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const IDENTIFIER = 'Magento';
    public const PROFILE_TYPE = 'customer';

    /**
     * @var string $endpoint
     */
    public $endpoint;

    /**
     * @var Instance $instanceHelper
     */
    protected $instanceHelper;

    /**
     * Sunshine constructor.
     * @param Context $context
     * @param Instance $instanceHelper
     */
    public function __construct(
        Context $context,
        Instance $instanceHelper
    ) {
        parent::__construct($context);
        $this->instanceHelper = $instanceHelper;
    }

    /**
     * Get relationships types
     *
     * @return stdClass|null
     * @throws ApiResponseException
     * @throws AuthException
     */
    public function getTypes()
    {
        $this->endpoint = 'api/Zendesk/relationships/types';
        return $this->all();
    }

    /**
     * Get users
     *
     * @return stdClass|null
     * @throws ApiResponseException
     * @throws AuthException
     */
    public function getUsers()
    {
        $this->endpoint = 'api/v2/users';
        return $this->all();
    }

    /**
     * Execute POST request
     *
     * @param array $data
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return stdClass|null
     * @throws ApiResponseException
     * @throws AuthException
     */
    public function post($data, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $apiClient = $this->instanceHelper->getZendeskApiInstance($scopeType, $scopeCode);
        return $apiClient->post($this->endpoint, $data);
    }

    /**
     * Update profile data
     *
     * @param array $data
     * @param string $email
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return stdClass|null
     * @throws ApiResponseException
     * @throws AuthException
     */
    public function postProfile($data, $email, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $type = $data['profile']['type'];
        $apiClient = $this->instanceHelper->getZendeskApiInstance($scopeType, $scopeCode);
        return $apiClient->put("api/v2/user_profiles?identifier=" . self::IDENTIFIER . ":$type:email:$email", $data);
    }

    /**
     * Execute PUT request
     *
     * @param string $key
     * @param array $data
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return stdClass|null
     * @throws ApiResponseException
     * @throws AuthException
     */
    public function put($key, $data, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $apiClient = $this->instanceHelper->getZendeskApiInstance($scopeType, $scopeCode);
        return $apiClient->put($this->endpoint . "/$key", $data);
    }

    /**
     * Get all data
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return stdClass|null
     * @throws ApiResponseException
     * @throws AuthException
     */
    public function all($scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $apiClient = $this->instanceHelper->getZendeskApiInstance($scopeType, $scopeCode);
        return $apiClient->get($this->endpoint);
    }

    /**
     * Execute GET request
     *
     * @param string $key
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return stdClass|null
     * @throws ApiResponseException
     * @throws AuthException
     */
    public function get($key, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $apiClient = $this->instanceHelper->getZendeskApiInstance($scopeType, $scopeCode);
        return $apiClient->get($this->endpoint . "/$key");
    }

    /**
     * Execute DELETE request
     *
     * @param string $key
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return stdClass|null
     * @throws ApiResponseException
     * @throws AuthException
     */
    public function delete($key, $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        $apiClient = $this->instanceHelper->getZendeskApiInstance($scopeType, $scopeCode);
        return $apiClient->get($this->endpoint . "/$key");
    }
}
