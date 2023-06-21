<?php

namespace Zendesk\Zendesk\ZendeskApi\Core;

use Zendesk\API\Exceptions\ApiResponseException;
use Zendesk\API\Exceptions\AuthException;

class Brands extends \Zendesk\API\Resources\Core\Brands
{
    /**
     * @inheritdoc
     *
     * Add routes specific to this subclass
     */
    protected function setUpRoutes()
    {
        parent::setUpRoutes();

        $this->setRoutes([
            'getBrands' => "{$this->resourceName}.json"
        ]);
    }

    /**
     * Get brands available to current agent
     *
     * @return \stdClass|null
     * @throws ApiResponseException
     * @throws AuthException
     */
    public function getBrands()
    {
        return $this->client->get($this->getRoute(__FUNCTION__));
    }
}
