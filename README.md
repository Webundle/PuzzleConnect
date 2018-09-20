# Puzzle User
**=========================================**

Puzzle bundle for managing basic and oauth authentication and also user accounts

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

`composer require webundle/puzzle-connect`

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Puzzle\ConnectBundle\PuzzleConnectBundle(),
        );

        // ...
    }

    // ...
}
```

### Step 3: Register the Routes

Load the bundle's routing definition in the application (usually in the `app/config/routing.yml` file):

# app/config/routing.yml
```yaml
puzzle_connect:
        resource: "@PuzzleConnectBundle/Resources/config/routing.yml"
```

### Step 4: Configure Puzzle OAuth options

Then, enable management bundle via admin modules interface by adding it to the list of registered bundles in the `app/config/config.yml` file of your project under:

```yaml
puzzle_connect:
    client_id:             client_id_value
    client_secret:         client_secret_value
    base_authorize_uri:    'http://account.puzzle.ci/oauth/v2/authorize'
    base_token_uri:        'http://account.puzzle.ci/oauth/v2/token'
    default_redirect_uri:  oauth
    default_scope:         'user'
    host_apis:             'http://apis.puzzle.ci'
```
