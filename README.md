WassaPushServerBundle
==================
The **WassaPushServerBundle** bundle allows you to send push notification to iOS and Android devices.
It use a custom library to send GCM notifications and [APNS-PHP](#https://github.com/immobiliare/ApnsPHP) for APNS notifications.

Installation
------------
Require the `wassafr/push-server-bundle` package in your composer.json and update
your dependencies.

    $ composer require duccio/apns-php dev-master --no-update
    $ composer require wassafr/push-server-bundle

Register the bundle in `app/AppKernel.php`:

```php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Wassa\MPSBundle\MPSBundle(),
    );
}
```

Import the routing definition in `routing.yml`:

```yaml
# app/config/routing.yml
wassa_mps_api:
    resource: "@WassaMPSBundle/Controller/API/"
    type: annotation
    prefix: /api/push
```

To enable the configuration, we suggest to add parameters to parameters.yml.dist so that you can change them easily when you deploy the bundle to multiple servers with different configurations:

```yaml
# app/config/parameters.yml.dist
    wassa_mps_gcm_api_key:
    wassa_mps_gcm_dry_run: false
    wassa_mps_apns_environment: sandbox
    wassa_mps_apns_prod_cert: ~
    wassa_mps_apns_sand_cert: ~
    wassa_mps_apns_ca_cert: ~
    wassa_mps_entity_manager: ~
```

```yaml
# app/config/parameters.yml
    wassa_mps_gcm_api_key: <gcm_key>
    wassa_mps_gcm_dry_run: false
    wassa_mps_apns_environment: sandbox
    wassa_mps_apns_prod_cert: <path_to_apns_prod_cert>
    wassa_mps_apns_sand_cert: <path_to_apns_sandbox_cert>
    wassa_mps_apns_ca_cert: <path_to_apns_rootca_cert>
    wassa_mps_entity_manager: ~
```

And finally:

```yaml
# app/config/config.yml
wassa_mps:
    gcm:
        api_key: "%wassa_mps_gcm_api_key%"
        dry_run: "%wassa_mps_gcm_dry_run%"
    apns:
        environment: "%wassa_mps_apns_environment%"
        prod_cert: "%wassa_mps_apns_prod_cert%"
        sand_cert: "%wassa_mps_apns_sand_cert%"
        ca_cert: "%wassa_mps_apns_ca_cert%"
    entity_manager: "%wassa_mps_entity_manager%"
```

Send Push
---------
```php
// AppBundle/Controller/YourController.php

$mps = $this->get('wassa_mps');
$pushData = new PushData();
$pushData->setGcmPayloadData($gcmPayloadData); // $gcmPayloadData is an associative array
$pushData->setGcmCollapseKey($gcmCollapsKey);
$pushData->setApnsText($apnsText);
$pushData->setApnsBadge($apnsBadge);
$pushData->setApnsCategory($apnsCategory);
$pushData->setApnsCustomProperties($apnsCustomProperties); // $apnsCustomProperties is an associative array
$pushData->setApnsExpiry($apnsExpiry);
$pushData->setApnsSound($apnsSound);
```

API
---
The bundle provide a simple API for device regisration.
The registration service should be called in POST with the following POST data:

```json
{
    "registrationToken": "<GCM_REGISTRATIONID_OR_APNS_DEVICETOKEN>",
    "platform": "<ios|android>",
    "customData": <CUSTOM_JSON_DATA>
}
```
