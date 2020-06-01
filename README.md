<a href="https://infifnisoftware.ro" target="_blank">
    <img src="https://infifnisoftware.ro/themes/custom/infifni/logo.svg" alt="infifni logo" height="200" />
</a>
<h1>
    Sylius EuPlătesc PLUGIN
    <br />
    License MIT
</h1>

<p>
This plugin works with EuPlătesc version 3, the HTTP POST variant where you make a POST request
to EuPlătesc transaction processor and EuPlătesc does a POST redirect back to an url that you specify.
Also the Sylius version must be at least 1.6.
</p>

## Installation

1. Run `composer require infifni/euplatesc-plugin`.

2. Add plugin dependencies to your `config/bundles.php` file:
    ```php
    // config/bundles.php
    return [
        // other lines
        new Infifni\SyliusEuPlatescPlugin\InfifniSyliusEuPlatescPlugin(),
    ];
    ```

3. Import routes in `config/routes/infifni_sylius_euplatesc_plugin.yml`:

```yaml
# config/routes/infifni_sylius_euplatesc_plugin.yml
infifni_sylius_euplatesc_plugin:
    resource: "@InfifniSyliusEuPlatescPlugin/Resources/config/routing.yml"
```

## Testing
```bash
$ composer install
$ cd tests/Application
$ yarn install
$ yarn run build
$ bin/console assets:install public -e test
$ bin/console doctrine:database:create -e test
$ bin/console doctrine:schema:create -e test
$ // cd back to plugin root dir
$ cd /root/dir/of/plugin
$ vendor/bin/behat --tags="~@javascript"
$ vendor/bin/phpspec run
```

## Settings

After receiving access to an EuPlătesc account you will need to set the return url, which is
the url where EuPlătesc does a POST request with details after payment.

Go to https://manager.euplatesc.ro/v3/index.php and fill the Success URL and Fail URL
with https://yourdomain.com/payment/euplatesc/notify