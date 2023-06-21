<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Model\SkuGroup;

use Digitalriver\DrPay\Api\SkuGroupApiClientInterface;
use Digitalriver\DrPay\Helper\Config;
use Laminas\Validator\ValidatorInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class ApiClient
 *
 * Provides functionality to interact with SKU Group API
 * @see https://www.digitalriver.com/docs/digital-river-api-reference/#tag/SkuGroups
 */
class ApiClient implements SkuGroupApiClientInterface
{
    private const REQUEST_KEY_LIMIT = 'limit';
    private const REQUEST_KEY_BEFORE = 'endingBefore';
    private const REQUEST_KEY_AFTER = 'startingAfter';
    private const SERVICE_NAME = 'sku-groups';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ValidatorInterface
     */
    private $responseValidator;

    /**
     * @param Config $config
     * @param LoggerInterface $logger
     * @param ValidatorInterface $responseValidator
     */
    public function __construct(
        Config $config,
        LoggerInterface $logger,
        ValidatorInterface $responseValidator
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->responseValidator = $responseValidator;
    }

    /**
     * Retrieves a list of Digital River Sku Groups
     *
     * @see https://www.digitalriver.com/docs/digital-river-api-reference/#tag/SkuGroups
     *
     * @param string|null $startingAfter
     * @param string|null $endingBefore
     * @param int $limit
     * @return array = [ <int> => [ 'id' => <string>, 'alias' => <string>]]
     * Alias is not a mandatory key and could be absent
     * @throws LocalizedException
     */
    public function getSkuGroups(?string $startingAfter = null, ?string $endingBefore = null, int $limit = 100): array
    {
        $data = [self::REQUEST_KEY_LIMIT => $limit];
        if ($startingAfter !== null) {
            $data[self::REQUEST_KEY_AFTER] = $startingAfter;
        }
        if ($endingBefore !== null) {
            $data[self::REQUEST_KEY_BEFORE] = $endingBefore;
        }
        $response = $this->config->doCurlList(self::SERVICE_NAME, $data);
        if ($this->responseValidator->isValid($response)) {
            return $response[self::RESPONSE_KEY_MESSAGE];
        }
        $uid = uniqid();
        foreach ($this->responseValidator->getMessages() as $error) {
            $this->logger->error(sprintf("%s, UID: %s", $error, $uid));
        }
        return array("error" => true, "message" => "SKU Groups API error. See logs for details. UID: " . $uid );
    }
}
