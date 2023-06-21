<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\Validator;

use Digitalriver\DrPay\Api\SkuGroupApiClientInterface as ApiClient;
use Laminas\Validator\ValidatorInterface;
use Magento\Framework\Webapi\Response;

/**
 * Class SkuGroupResponse
 * Validates /sku-groups response
 */
class SkuGroupResponse implements ValidatorInterface
{
    /**
     * @var array
     */
    private $response = [];
    /**
     * @var array
     */
    private $errors = [];

    /**
     * @param array $value
     * @return bool
     */
    public function isValid($value): bool
    {
        // Re-initialize the error list for every validation
        $this->errors = [];
        if (!is_array($value)) {
            $this->errors[] = 'Expected the value to be an array';
            return false;
        }
        $this->response = $value;
        if (!$this->checkRequiredField()) {
            return false;
        }
        $this->validateStatus();
        $this->validateSuccessStatus();
        $this->validateMessage();
        return empty($this->errors);
    }

    /**
     * Check if response's status is correct
     */
    private function validateStatus(): void
    {
        if ($this->response[ApiClient::RESPONSE_KEY_STATUS] !== Response::HTTP_OK) {
            $this->errors[] = "Incorrect response status code: " . $this->response[ApiClient::RESPONSE_KEY_STATUS];
            if (isset($this->response[ApiClient::RESPONSE_KEY_MESSAGE]) &&
                is_string($this->response[ApiClient::RESPONSE_KEY_MESSAGE])) {
                $this->errors[] = "Response error message: " . $this->response[ApiClient::RESPONSE_KEY_MESSAGE];
            }
        }
    }

    /**
     * Check if a request was completed successfully
     */
    private function validateSuccessStatus(): void
    {
        if ($this->response[ApiClient::RESPONSE_KEY_SUCCESS] !== true) {
            $this->errors[] = "The request for SKU groups failed";
        }
    }

    /**
     * Check if all required fields are present in a request
     *
     * @return bool
     */
    private function checkRequiredField(): bool
    {
        $requiredFields = [
            ApiClient::RESPONSE_KEY_SUCCESS,
            ApiClient::RESPONSE_KEY_STATUS,
        ];
        $isValid = true;
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $this->response)) {
                $this->errors[] = sprintf("Required field %s is not present in the response", $field);
                $isValid = false;
            }
        }
        return $isValid;
    }

    /**
     * Check if `message` field has a correct structure
     */
    private function validateMessage()
    {
        if (!array_key_exists(ApiClient::RESPONSE_KEY_MESSAGE, $this->response)) {
            $this->errors[] = "`message` field is not present in response";
            return;
        }
        if (!is_array($this->response[ApiClient::RESPONSE_KEY_MESSAGE])) {
            $this->errors[] = "`message` field has incorrect format";
        }
    }

    /**
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->errors;
    }
}
