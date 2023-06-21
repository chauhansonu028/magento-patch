<?php

namespace Zendesk\Zendesk\Plugin\Config\Model\Config\Structure\Element;

use Magento\Config\Model\Config\Structure\Element\Group as OriginalGroup;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Zendesk\API\Exceptions\AuthException;
use Zendesk\Zendesk\Helper\Api;
use Zendesk\Zendesk\Helper\Config;
use Zendesk\Zendesk\Helper\ScopeHelper;

class Group
{
    public const GROUP_ID = 'brand_mapping';

    /**
     * @var Api
     */
    protected $apiHelper;

    /**
     * @var StoreRepositoryInterface
     */
    protected $storeRepository;

    /**
     * @var null|StoreInterface[]
     */
    protected $stores;

    /**
     * @var ScopeHelper
     */
    protected $scopeHelper;

    /**
     * Group constructor.
     * @param Api $apiHelper
     * @param ScopeHelper $scopeHelper
     * @param StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        Api                      $apiHelper,
        ScopeHelper              $scopeHelper,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->apiHelper = $apiHelper;
        $this->scopeHelper = $scopeHelper;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Add brand mapping config fields.
     *
     * @param OriginalGroup $subject
     * @param callable $proceed
     * @param array $data
     * @param string $scope
     *
     * @return mixed
     * @throws AuthException
     */
    public function aroundSetData(OriginalGroup $subject, callable $proceed, array $data, string $scope)
    {
        // This method runs for every group.
        // Add a condition to check for the one to which we're
        // interested in adding fields.
        if (self::GROUP_ID == $data['id']) {
            $dynamicFields = $this->getDynamicFields();

            if (!empty($dynamicFields)) {
                $children = isset($data['children']) ? $data['children'] : [];

                $children += $dynamicFields;

                $data['children'] = $children;
            }
        }

        return $proceed($data, $scope);
    }

    /**
     * Get store views as options array.
     *
     * @param string $scopeType
     * @param string|null $scopeCode
     * @return array|StoreInterface[]|null
     */
    protected function getStoreOptions(
        $scopeType = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if (null === $this->stores) {
            $this->stores = [];
            $stores = [];
            $allStores = $this->storeRepository->getList();

            if ($scopeType == ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $stores = $allStores;
            } else {
                if ($scopeType == ScopeInterface::SCOPE_WEBSITE) {
                    foreach ($allStores as $store) {
                        if ($store->getWebsiteId() == $scopeCode) {
                            $stores[] = $store;
                        }
                    }
                } else {
                    foreach ($allStores as $store) {
                        if ($store->getId() == $scopeCode) {
                            $stores[] = $store;
                        }
                    }
                }
            }

            if ($stores) {
                foreach ($stores as $store) {
                    if (\Magento\Store\Model\Store::ADMIN_CODE == $store->getCode()) {
                        continue;
                    }

                    $this->stores[] = [
                        'value' => $store->getId(),
                        'label' => $store->getName(),
                    ];
                }
            }
        }

        return $this->stores;
    }

    /**
     * Get brand dynamic config fields array.
     *
     * @throws AuthException
     *
     * @return array
     */
    protected function getDynamicFields()
    {
        try {
            list($scopeType, $scopeId) = $this->scopeHelper->getScope();
            $this->apiHelper->tryAuthenticate($scopeType, $scopeId);
        } catch (AuthException $e) {
            // not configured -- nothing to do.
            return [];
        }

        $api = $this->apiHelper->getZendeskApiInstance($scopeType, $scopeId);

        $brands = $api->brands()->getBrands();

        $storeOptions = $this->getStoreOptions($scopeType, $scopeId);

        if (count($brands->brands) < 2 || count($storeOptions) < 2) {
            return []; // No need for this UI if there is only one store or only one brand
        }

        $dynamicConfigFields = [];

        foreach ($brands->brands as $index => $brand) {
            $configId = Config::BRAND_FIELD_CONFIG_PATH_PREFIX . $brand->id;

            $dynamicConfigFields[$configId] = [
                'id' => $configId,
                'type' => 'multiselect',
                'sortOrder' => ($index * 10), // Generate unique and deterministic sortOrder values
                'showInDefault' => '1',       // In this case, only show fields at default scope
                'showInWebsite' => '1',
                'showInStore' => '1',
                'label' => $brand->name,
                'options' => [                // Since this is a multiselect, generate options dynamically.
                    'option' => $storeOptions,
                ],
                'comment' => __(
                    'Select store(s) to map to brand <strong>%1</strong>.',
                    $brand->name
                ),
                '_elementType' => 'field',
                'path' => Config::BRAND_FIELD_GROUP_PREFIX,
            ];
        }

        return $dynamicConfigFields;
    }
}
