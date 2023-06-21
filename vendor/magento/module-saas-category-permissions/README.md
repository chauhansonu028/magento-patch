## Release notes

This extension exports raw data of Category Permissions Adobe Commerce feature.  
System configuration that may have impact on Category Permissions feature is exported within the same contract.

## API

* REST endpoint responsible for accepting
  data: `https://<feed-ingestion-service-url>/feeds/categories/permissions/v1/{{environmentId}}`
* Payload contract is described under CategoryPermission type in et_schema.xml

**Payload example**

```json
[
  {
    "id": {
      "websiteCode": "FALLBACK_WEBSITE",
      "customerGroupCode": "FALLBACK_CUSTOMER_GROUP",
      "categoryId": "ADD_TO_CART"
    },
    "type": "GLOBAL",
    "permission": {
      "ADD_TO_CART": "ALLOW"
    },
    "deleted": false,
    "modifiedAt": "2023-01-06 23:36:51"
  },
  {
    "id": {
      "websiteCode": "base",
      "customerGroupCode": "356a192b7913b04c54574d18c28d46e6395428ab",
      "categoryId": "3"
    },
    "type": "STANDARD",
    "permission": {
      "DISPLAYED": "ALLOW",
      "DISPLAY_PRODUCT_PRICE": "DENY",
      "ADD_TO_CART": "DENY"
    },
    "deleted": false,
    "modifiedAt": "2023-01-06 23:36:51"
  }
]
```

## Category Permissions Data Exporter extension detail


There are different aspects of the CategoryPermissionDataExporter and SaaSCategoryPermissions modules to understand:

* Data is gathered by the *CategoryPermissionDataExporter* module into the following tables:
    * `catalog_data_exporter_category_permissions`: stores raw data in prepared format, including system configuration
* Cron job `submit_category_permissions_feed` are created to run every minute for packing the data and send it over the wire
  to the Feed Ingestion Service:
* Once the data is sent to the Feed Ingestion Service, a hashed data is stored in the following tables:
    * `catalog_category_permissions_data_submitted_hash`: stores the last time a given feed was sent. Responsible to store hash of submitted data to prevent sending the same data twice over the wire
