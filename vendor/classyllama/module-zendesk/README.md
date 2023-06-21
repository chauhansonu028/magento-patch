# Zendesk Integration Extension
   
   # Setup
   
   1. In your Zendesk Support account, generate an API token as described in the [Generating a new API token documentation](https://support.zendesk.com/hc/en-us/articles/226022787-Generating-a-new-API-token).
   2. If you are using Magento 2.4.4, enable Integration tokens (more information on the [Magento documentation page](https://devdocs.magento.com/guides/v2.4/get-started/authentication/gs-authentication-token.html#integration-tokens)):
   ```
   bin/magento config:set oauth/consumer/enable_integration_as_bearer 1
   ```
   3. In the Magento backend, click the _Zendesk_ top-level link in main navigation.
   4. Expand the _General_ section, then populate the following fields.
       1. `Zendesk Domain`: The Zendesk support domain. For example, _yourdomain_.zendesk.com .
       2. `Agent Email Address`: Your agent email address.
       3. `Agent Token`: The Zendesk API token generated in step 1.
   5. Click the _Save Config_ button on the top-right.
   6. Once the page has reloaded, click the _Test Connection_ button in the _General_ section to confirm that the Zendesk API can be successfully accessed.


#Setup code sniffer
Install code sniffer like this. Any developer working on the extension should do this.
```
sudo chmod +x .githooks/*
git config core.hooksPath .githooks

git clone git@github.com:magento/magento-coding-standard.git
cd magento-coding-standard
composer install

```
Now, whenever you commit, any files in the commit will be checked with the codesniffer.
   
# Installation 
```
composer config repositories.zendesk/module-zendesk git git@bitbucket.org:classyllama/zendesk_zendesk.git
composer require zendesk/module-zendesk
```

##Zendesk Sunshine API Documentation

https://developer.zendesk.com/rest_api/docs/sunshine/introduction
Description

#Sunshine

The Magento store will need to listen to specific events in order to pass relevant data to Zendesk Sunshine. Magento will need to listen to the following:

 - Item added to shopping cart
 - Item removed from shopping cart
 - Refund/Return status change
 - Checkout Started
 - Customer updated or customer created
 - Customer deleted
 - Order placed or order updated
 - Order canceled
 - Order paid
 - Order shipped

#Deploying package to Magento Marketplace
 - Download repository as zip file.
 - Unzip and remove .idea and .githooks directories.
 - re-zip and it is ready. 
 - (Make sure your compose.json version is correct)
