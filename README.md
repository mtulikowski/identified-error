# Smartgroup Identified Error Bundle

A Symfony bundle that assigns an identifier while handling an exception, adds that identifier to Monolog records, and displays it with the HTTP status on the error page.

## Requirements

- PHP 8.2 or newer
- Symfony FrameworkBundle and TwigBundle 6.4 or 7.x
- Symfony MonologBundle 3.x and Monolog 3.x

The application must already configure its own Monolog handlers and destinations. This bundle does not configure handlers, file rotation, or retention.

## Installation

Install the package with Composer:

```shell
composer require smartgroup/identified-error
```

If the application uses Symfony Flex, Flex automatically registers this package as a bundle because its Composer type is `symfony-bundle`.

Without Symfony Flex, register the bundle manually in `config/bundles.php`:

```php
return [
    // ...
    Smartgroup\IdentifiedErrorBundle\IdentifiedErrorBundle::class => ['all' => true],
];
```

No Flex recipe is needed for bundle registration alone. The application must still configure its own Monolog handlers and destinations.

## Configuration

The bundle is enabled by default. To disable all its services and integrations:

```yaml
# config/packages/identified_error.yaml
identified_error:
    enabled: false
```

When enabled, the bundle sets `framework.error_controller` to its controller if the application has not configured a custom error controller. It registers a `kernel.exception` listener and a Monolog processor. The processor adds `extra.error_id` only after an identifier has been generated for the current main request.

The identifier has the form `YYYYMMDDTHHMMSSZ-<32 hexadecimal characters>`. It is available in the Twig context as `identifier`, alongside `status_code`.

## Environments

The bundle is enabled in every environment where it is registered. Its generic page is suitable as a production fallback; Symfony's development exception pages remain available if the bundle is disabled in `dev` or `test` using environment-specific configuration. For example:

```yaml
# config/packages/dev/identified_error.yaml
identified_error:
    enabled: false
```

## Customize the error page

Override the generic template at:

```text
templates/bundles/IdentifiedErrorBundle/Exception/error.html.twig
```

You can also provide a status-specific template, such as `error404.html.twig`. The bundle's default template is available as `@IdentifiedError/Exception/error.html.twig`. Templates receive `status_code` and `identifier`. The default page has no application branding or external assets.

## License

MIT. See [LICENSE](LICENSE).

## Release

Create a Git tag for each release using a semantic version, for example `v1.0.0`, and push the tag to the repository. Packagist can then publish the tagged version when the repository is connected to the package. Do not add a fixed `version` field to `composer.json`; Composer reads the release version from the tag.
