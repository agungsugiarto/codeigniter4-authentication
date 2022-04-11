# CodeIgniter4 Authentication

[![tests](https://github.com/agungsugiarto/codeigniter4-authentication/actions/workflows/php.yml/badge.svg)](https://github.com/agungsugiarto/codeigniter4-authentication/actions/workflows/php.yml)
[![Latest Stable Version](https://poser.pugx.org/agungsugiarto/codeigniter4-authentication/v)](https://github.com/agungsugiarto/codeigniter4-authentication/releases)
[![Total Downloads](https://poser.pugx.org/agungsugiarto/codeigniter4-authentication/downloads)](https://packagist.org/packages/agungsugiarto/codeigniter4-authentication/stats)
[![Latest Unstable Version](https://poser.pugx.org/agungsugiarto/codeigniter4-authentication/v/unstable)](https://packagist.org/packages/agungsugiarto/codeigniter4-authentication)
[![License](https://poser.pugx.org/agungsugiarto/codeigniter4-authentication/license)](https://github.com/agungsugiarto/codeigniter4-authentication/blob/master/LICENSE.md)

## About
The `codeigniter4\authentication` component provides an API for authentication and
includes concrete authentication adapters for common use case scenarios.

- Inspired from https://github.com/lonnieezell/codigniter-shield
- Most inspired from auth by laravel https://github.com/illuminate/auth

## Upgrade from v1.x to 2.x
### Composer Dependencies
You should update the following dependencies in your application's composer.json file:

`agungsugiarto/codeigniter4-authentication` to `^2.0`

### User Entity

Open class `App\Entities\User` add interface and trait to implement.
```diff
namespace Fluent\Auth\Entities;

- use CodeIgniter\Entity;
+ use CodeIgniter\Entity\Entity;
use Fluent\Auth\Contracts\AuthenticatorInterface;
+ use Fluent\Auth\Contracts\AuthorizableInterface;
use Fluent\Auth\Contracts\HasAccessTokensInterface;
use Fluent\Auth\Contracts\ResetPasswordInterface;
use Fluent\Auth\Contracts\VerifyEmailInterface;
use Fluent\Auth\Facades\Hash;
use Fluent\Auth\Traits\AuthenticatableTrait;
use Fluent\Auth\Traits\AuthorizableTrait;
use Fluent\Auth\Traits\CanResetPasswordTrait;
use Fluent\Auth\Traits\HasAccessTokensTrait;
use Fluent\Auth\Traits\MustVerifyEmailTrait;

class User extends Entity implements
    AuthenticatorInterface,
+   AuthorizableInterface,
    HasAccessTokensInterface,
    ResetPasswordInterface,
    VerifyEmailInterface
{
    use AuthenticatableTrait;
+   use AuthorizableTrait;
    use CanResetPasswordTrait;
    use HasAccessTokensTrait;
    use MustVerifyEmailTrait;
}
```

### AuthServiceProvider

Open `App\Providers\AuthServiceProvider`
```diff
namespace Fluent\Auth;

+ use Fluent\Auth\Facades\Gate;
use Fluent\Auth\AbstractServiceProvider;

class AuthServiceProvider extends AbstractServiceProvider
{
+   /**
+    * The policy mappings for the application.
+    *
+    * @var array<class-string, class-string>
+    */
+   protected static $policies = [];

    /**
     * {@inheritdoc}
     */
    public static function register()
    {
+        static::registerPolicies();
    }
}
```

## Documentation
- [Authentication](docs/en/authentication.md)
- [Authorization](docs/en/authorization.md)

## Community Authentication Guards
- JWT (JSON Web Token) - [agungsugiarto/codeigniter4-authentication-jwt](https://github.com/agungsugiarto/codeigniter4-authentication-jwt)

## Authentication Demo
- [codeigniter4-authentication-demo](https://github.com/agungsugiarto/codeigniter4-authentication-demo)

Changelog
--------
Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing
Contributions are very welcome.

## License

Released under the MIT License, see [LICENSE](LICENSE.md).
