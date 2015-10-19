# Meet-Magento Poland 2015, Poznan
## Dipping your Toes in Magento 2 Waters
### Magento 2 Developer Mini-Workshop by Vinai Kopp

#### 19. October 2015, 10:00 AM

### Overview

##### Goal: The MMPL15_ProductStatus Module

* CLI command to show product status for SKU matches
* CLI command to disable and enable products
* REST API exposing the same functionality, accessible only by admins
  
##### Aspects of Magento 2 development covered

* Dependency Injection (DI)
* Unit Tests
* Design by Contract (PHP Interfaces)
* Basic Module Structure
* API Service Contracts
* Repository usage
* Web-API and ACL
* Token based REST API authentication and access

##### NOT covered

* Plugins
* Routing and Actions
* Layout
* Virtual Types
* Repository implementation
* Data API
* Events
* JS Framework
* Static-, Integration-, Functional-, Performance- and JS-Tests 
* Magento 2 Modules...
* So much more...

### Coding

#### Create Skeleton Module (0.0.1)

* Create `app/code/MMPL15/ProductStatus/etc/module.xml`
* Create `app/code/MMPL15/ProductStatus/registration.php`
* Run `bin/magento module:status` -> present but disabled
* Run `bin/magento module:enable MMPL15_ProductStatus`
* Run `bin/magento setup:upgrade`
* Run `bin/magento module:status` -> present and enabled

#### Create ShowProductStatusCommand (0.0.2)

* Create `app/code/MMPL15/ProductStatus/Test/Unit/Console/Command/ShowProductStatusCommandTest.php`
* Test the class `ShowProductStatusCommand` exists
* In the Magento base directory, run `vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/MMPL15/ProductStatus/Test/Unit`
* Create `app/code/MMPL15/ProductStatus/Console/Command/ShowProductStatusCommand.php`
* Test the class is a `Symfony\Component\Console\Command\Command`
* Test it has a name `catalog:product:status`
* Test it has a description
* Test it takes a required SKU argument
* Test it delegates to the product status adapter
* Test it displays exceptions as error messages
* Test it displays a confirmation message if there is no exception
* Test it displays a message if no products matched the given SKU
* Test it displays the status for all returned products

#### Create ProductStatusAdapterInterface

* Create `app/code/MMPL15/ProductStatus/LibraryApi/ProductStatusAdapterInterface.php`

#### Refactor test and command to use interface

* Import new interface, remove stub method declaration
* Add ENABLED and DISABLED constants to interface and use them in the test instead of hardcoded status strings

#### Create ProductStatusAdapter (0.0.3)

* Create `app/code/MMPL15/ProductStatus/Test/Unit/Model/ProductStatusAdapterTest.php`
* Test the class exists
* Test the class implements `ProductStatusAdapterInterface`
* Test it throws an exception in SKU is not a string or empty
* Test it queries a product repository
* Test it returns an empty array if there is no match
* Test it translates the product repository search results into the return array format

#### Create DI configuration (0.0.4)

* Create `app/code/MMPL15/ProductStatus/etc/di.xml`
* Add preference for `ProductStatusAdapterInterface`

#### Create DisableProductCommand

* Create `app/code/MMPL15/ProductStatus/Test/Unit/Console/Command/DisableProductCommandTest.php`
* Test the class `DisableProductCommand` exists
* Test it is a `Symfony\Component\Console\Command\Command`
* Test it has the right name
* Test it has a description
* Test it takes a required SKU argument
* Test it delegates to the product status adapter
* Test it displays exceptions as error messages
* Test it displays a confirmation message if there was no exception

#### Implement ProductStatusAdapterInterface::disableProductWithSku

* Add a stub implementation to the class
* Make existing tests pass
* Test it throws an exception if the SKU is not a string or empty
* Test it throws an exception if the product already is disabled
* Test it disables an existing product

#### Add DI configuration for new command (0.0.5)

* Add second line to `Magento\Framework\Console\CommandList` DI configuration in `etc/di.xml`
* If still needed, workaround the issue "*Area code not set: Area code must be set before starting a session.*" by injecting `Magento\Framework\App\State` and setting the area code `adminhtml` in `ProductStatusAdapter::__construct()`.

#### Create API resource ProductStatusManagement

* Create `app/code/MMPL15/ProductStatus/Api/ProductStatusManagementInterface.php`
* Add one public method `get($sku)` returning a string
* Test the class `MMPL15/ProductStatus/Model/ProductStatusManagement` exists
* Test it implements the interface
* Test it delegates to a new method of the product status adapter `getStatusBySku()`

#### Implement ProductStatusAdapterInterface::getStatusBySku

* Add a stub implementation to `ProductStatusAdapter`
* Make existing tests pass
* Test it throws an exception if the SKU is not a string or is empty
* Test it translates the products status to the matching status string

#### Add configuration for Web-API

* Add preference for `\MMPL15\ProductStatus\Api\ProductStatusManagementInterface`
* Add `app/code/MMPL15/ProductStatus/etc/acl.xml`
* Add a new resource admin -> catalog -> catalog_inventory -> product_status to the ACL
* Add `app/code/MMPL15/ProductStatus/etc/webapi.xml`
* Add a `GET` route to `/V1/mmpl15/product/status/:sku` mapping to the `ProductStatusManagementInterface::get` method

#### Test REST API with curl CLI (0.0.6)

* Get a token

```
curl -X POST "http://mmpl15.dev/rest/V1/integration/admin/token" \
  -H "content-type:application/json" \
  -d '{"username":"admin", "password":"<PASSWORD>"}'
```
* Send a request to the new resource

```
curl -X GET "http://mmpl15.dev/rest/V1/mmpl15/product/status/<SKU>" \
    -H "content-type:application/json" \
    -H "Authorization: Bearer <TOKEN>"
```

#### Add EnableProductCommand (0.0.7)

By following steps similar to the examples above, implement a CLI command to enable products.

#### Add PUT route for product status REST resource (0.0.8)

Add a new REST API resource to set the status of products.
