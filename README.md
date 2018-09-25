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
### Step 5: Security

Then, configure security in the `app/config/security.yml` file of your project under:

```yaml
security:
    encoders:
        ...
        Puzzle\ConnectBundle\Entity\User: 
            algorithm:                        sha512
            encode_as_base64:                 false
            iterations:                       1
            
    role_hierarchy:
        ROLE_ADMIN:                           ROLE_USER
        ROLE_SUPER_ADMIN:                     [ROLE_ALLOWED_TO_SWITCH, ROLE_ADMIN]

    providers:
        ...
        connect_provider:
            id:                               puzzle_connect.provider.user
             
    firewalls:
        
        main:
            host:                             '%host_account%'
            pattern:                          '^/'
            entry_point:                     puzzle_connect.security.authentication.form_entry_point
            entry_point:                      null
            logout_on_user_change:            true
            form_login:
                provider:                     connect_provider
                check_path:                   login_check
                login_path:                   login
                success_handler:              puzzle_connect.handler.authentication_success
                username_parameter:           _username
                password_parameter:           _password
                csrf_parameter:               _csrf_token
                csrf_token_id:                authenticate
                post_only:                    true
                remember_me:                  true
                require_previous_session:     true
            switch_user:
                provider:                     connect_provider
                parameter:                    _swu
                role:                         ROLE_ALLOWED_TO_SWITCH
            remember_me:
                secret:                       '%secret%'
                path:                         /
                domain:                       '%host_admin%'
                secure:                       false
                httponly:                     true
                lifetime:                     31536000
                remember_me_parameter:        _remember_me
            logout:
                path:                         logout
                target:                       login
                invalidate_session:           true
                delete_cookies:
                    a:                        { path: /, domain: ~ }
            anonymous:                        true

    access_control:
        ...
        - { path: ^/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/oauth, roles: IS_AUTHENTICATED_ANONYMOUSLY, host: '%host_admin%' }
        - { path: ^/, roles: ROLE_ADMIN, host: '%host_admin%' }

```

