<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Api;

/**
 * Interface SkuGroupApiInterface
 * @api
 */
interface SkuGroupApiClientInterface
{
    public const RESPONSE_KEY_MESSAGE = 'message';
    public const RESPONSE_KEY_SUCCESS = 'success';
    public const RESPONSE_KEY_STATUS = 'statusCode';

    /**
     * Retrieves a list of Digital River Sku Groups
     *
     * @param string|null $startingAfter
     * @param string|null $endingBefore
     * @param int $limit
     * @return array = [ <int> => [ 'id' => <string>, 'alias' => <string>]]
     * Alias is not a mandatory key and could be absent
     */
    public function getSkuGroups(?string $startingAfter = null, ?string $endingBefore = null, int $limit = 100): array;
}
