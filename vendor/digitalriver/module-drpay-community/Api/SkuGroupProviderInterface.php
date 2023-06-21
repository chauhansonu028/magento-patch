<?php
declare(strict_types=1);

namespace Digitalriver\DrPay\Api;

/**
 * Interface SkuGroupProviderInterface
 * @api
 */
interface SkuGroupProviderInterface
{
    /**
     * Returns a list of all available sku groups
     * Alias is not a mandatory key and could be absent
     *
     * @return array = [ <int> => [ 'id' => <string>, 'alias' => <string>]]
     */
    public function getSkuGroups(): array;
}
