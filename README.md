# Smartgroup Identified Error Bundle

Symfony bundle that assigns an identifier to handled exceptions, adds it to Monolog records, and displays it on the error page.

## Requirements

- PHP 8.2 or newer
- Symfony FrameworkBundle and TwigBundle 6.4 or 7.x
- Symfony MonologBundle 3.x and Monolog 3.x

## Install from this repository

The bundle currently lives in the application's `_bundle` directory. Add a local path repository to the application's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "_bundle",
            "options": {
                "symlink": true
            }
        }
    ]
}
```

Then require it with the project's local Composer executable:

```text
php composer.phar require smartgroup/identified-error
```

Register `Smartgroup\IdentifiedErrorBundle\IdentifiedErrorBundle` in `config/bundles.php` for the environments where it should be active. When publishing the package, a Symfony Flex recipe can automate bundle registration.

## Default behavior

The bundle registers an exception listener, a Monolog processor, and an error controller. It configures `framework.error_controller` automatically. It does not add or replace Monolog handlers: the application remains responsible for deciding where and how log records are written.

For each handled exception, the bundle generates an identifier in the form `YYYYMMDDTHHMMSSZ-<32 hex characters>`. The identifier is added to Monolog's `extra.error_id` and passed to the error template as `identifier`, alongside `status_code`.

The bundled generic template is used by default. To disable the bundle's services and automatic error controller configuration, set:

```yaml
identified_error:
    enabled: false
```

## Customize the error page

Override the bundled template in the application at:

```text
templates/bundles/IdentifiedErrorBundle/Exception/error.html.twig
```

The template receives `status_code` and `identifier`. The bundle does not publish CSS or assume an application brand; add styles and assets in the application template as needed.
