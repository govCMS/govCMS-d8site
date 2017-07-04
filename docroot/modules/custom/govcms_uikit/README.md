# govCMS UIKit

This module provides utilities to override generated markup so that it adheres to the sepcification provided by the [DTA UI Kit](This module provides utilities to override generated markup so that it adheres to the sepcification provided by the DTA UI Kit.
).

# Installation

## Manual

```
cd modules/custom
git clone --branch <release> [repo]
```

# Dependencies

- UI Kit enabled theme

# Usage

The module integrates with the Drupal API to alter the render process where required or it provides a text format which can be enabled for your content team.

## Available services

- `govcms.uikit.table`: Service to alter a table render array.

## Accessing the service in your custom module

### Dependency injection

```
service:
  my_module:
    class: Class\Namespace\Class
    arguments: ['@govcms.uikit.{service}']
```

### Drupal service manager

``` php
$service = Drupal::service('govcms.uikit.{service}');
$service->alter($data);
```

## PSR-1

``` php
use Drupal\govcms_uikit\Service\{Service}
{Service}::alter();
```

