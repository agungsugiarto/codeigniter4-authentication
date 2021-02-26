## Table of Content

- [Introduction](#introduction)
    - [Database Considerations](#introduction-database-considerations)
    - [Ecosystem Overview](#ecosystem-overview)
- [Authentication Quickstart](#authentication-quickstart)
    - [Install A Starter Kit](#install-a-starter-kit)
    - [Retrieving The Authenticated User](#retrieving-the-authenticated-user)
    - [Protecting Routes](#protecting-routes)
- [Manually Authenticating Users](#authenticating-users)
    - [Remembering Users](#remembering-users)
    - [Other Authentication Methods](#other-authentication-methods)
- [Logging Out](#logging-out)
- [Password Confirmation](#password-confirmation)
    - [Configuration](#password-confirmation-configuration)
    - [Routing](#password-confirmation-routing)
    - [Protecting Routes](#password-confirmation-protecting-routes)
- [Adding Custom Guards](#adding-custom-guards)
- [The User Provider Contract](#the-user-provider-contract)
- [The Authenticatable Contract](#the-authenticatable-contract)

<a name="introduction"></a>
## Introduction

Many web applications provide a way for their users to authenticate with the application and "login". Implementing this feature in web applications can be a complex and potentially risky endeavor. For this reason, codeigniter4-authentication strives to give you the tools you need to implement authentication quickly, securely, and easily.

At its package authentication facilities are made up of "guards" and "providers". Guards define how users are authenticated for each request. For example, CodeIgniter4 ships with a `session` guard which maintains state using session storage and cookies.

Providers define how users are retrieved from your persistent storage, codeigniter4-authentication ships with support for retrieving users using [Model](https://codeigniter4.github.io/userguide/models/model.html) and the `Database` query builder. However, you are free to define additional providers as needed for your application.

Your application's authentication configuration file is located at `app/Config/Auth.php`. This file contains several well documented options for tweaking the behavior of authentication services.

<a name="introduction-database-considerations"></a>
### Database Considerations

By default, codeigniter4-authentication includes an `app\Models\UserModel` [Model](https://codeigniter4.github.io/userguide/models/model.html) in your `app/Models` directory. This model may be used with the default Model authentication driver. If your application is not using Model, you may use the `database` authentication provider which uses the CodeIgniter4 query builder.

When building the database schema for the `app\Models\UserModel` model, make sure the password column is at least 60 characters in length.

Also, you should verify that your `users` (or equivalent) table contains a nullable, string `remember_token` column of 100 characters. This column will be used to store a token for users that select the "remember me" option when logging into your application.

<a name="ecosystem-overview"></a>
### Ecosystem Overview

First, consider how authentication works. When using a web browser, a user will provide their username and password via a login form. If these credentials are correct, the application will store information about the authenticated user in the user's [session](https://codeigniter4.github.io/userguide/libraries/sessions.html). A cookie issued to the browser contains the session ID so that subsequent requests to the application can associate the user with the correct session. After the session cookie is received, the application will retrieve the session data based on the session ID, note that the authentication information has been stored in the session, and will consider the user as "authenticated".

<a name="built-in-browser-authentication-services"></a>
#### Built-in Browser Authentication Services

codeigniter4-authentication includes built-in authentication and session services which are typically accessed via the `Auth` and `Session`. These features provide cookie based authentication for requests that are initiated from web browsers. They provide methods that allow you to verify a user's credentials and authenticate the user. In addition, these services will automatically store the proper authentication data in the user's session and issue the user's session cookie. A discussion of how to use these services is contained within this documentation.

<a name="authentication-quickstart"></a>
## Authentication Quickstart

> If you would like to integrate with codeigniter4-authentication directly, check out the documentation on [manually authenticating users](#authenticating-users).

<a name="install-a-starter-kit"></a>
### Install A Starter Kit

By default, codeigniter4-authentication includes an auth scaffolding is a minimal, simple implementation of all of codeigniter4-authentication features, including login, registration, password reset, email verification, and password confirmation.

### 1. Install package dependency
```sh
composer require agungsugiarto/codeigniter4-authentication
```

### 2. Publish auth functionality
```sh
php spark auth:publish
```

### 3. Migrate database
```sh
php spark migrate
```
### 4. Generates a new encryption key and writes it in an `.env` file.
```sh
php spark key:generate
```

### 5. Registering auth routes
Open your config routes located at `app/Config/Routes` add this line:
```php
\Fluent\Auth\Facades\Auth::routes();
```

The `routes` method accept an array, the full key for routes array `register`, `reset`, `confirm` and `verify` you can disable this specific route with key match from array, example key register:
```php
\Fluent\Auth\Facades\Auth::routes([
    'register' => false,
]);
```

We also added example routes dashboard, this route will be redirect to dashboard if login is successfully, in same file `Routes` add this line:
```php
$routes->group('dashboard', ['filter' => 'auth:web'], function ($routes) {
    $routes->get('/', 'Home::dashboard', ['filter' => 'verified']);
    $routes->get('confirm', 'Home::confirm', ['filter' => 'confirm']);
});
```

### 6. Registering filter
Open `app\Config\FIlters` see property with `aliases` and add this array to register filter:
```php
public $aliases = [
    // ...
    'auth'     => \Fluent\Auth\Filters\AuthenticationFilter::class,
    'confirm'  => [
        \Fluent\Auth\Filters\AuthenticationFilter::class,
        \Fluent\Auth\Filters\ConfirmPasswordFilter::class,
    ],
    'guest'    => \Fluent\Auth\Filters\RedirectAuthenticatedFilter::class,
    'throttle' => \Fluent\Auth\Filters\ThrottleFilter::class,
    'verified' => \Fluent\Auth\Filters\EmailVerifiedFilter::class,
];
```

Now you can try the codeigniter4-authentication, open the browser to see what happen. See routes with command for detail:
```sh
php spark routes
```

### 7. Registering Event dispatcher
This event will be dispatch to send reset password and verify email, you are free to implement this dispatcher example
using twilio service to send sms. Open `app\Config\Events` and add this line:
```php
Events::on(\Fluent\Auth\Contracts\VerifyEmailInterface::class, function ($email) {
    (new \App\Notifications\VerificationNotification($email))->send();
});

Events::on(\Fluent\Auth\Contracts\ResetPasswordInterface::class, function ($email, $token) {
    (new \App\Notifications\ResetPasswordNotification($email, $token))->send();
});
```

### 8. Optional Registering auth garbage collector
Open `app\Config\Toolbar` see property `collectors` add this collector
```php
public $collectors = [
    // ...
    \Fluent\Auth\Collectors\AuthCollector::class,
];
```

<a name="retrieving-the-authenticated-user"></a>
## Retrieving The Authenticated User

After installing an authentication starter kit and allowing users to register and authenticate with your application, you will often need to interact with the currently authenticated user. While handling an incoming request, you may access the authenticated user via the `Auth` services `user` method:
```php
use Fluent\Auth\Config\Services;

// Retrieve the currently authenticated user...
$user = Services::auth()->user();

// Retrieve the currently authenticated user's ID...
$id = Services::auth()->id();
```

Alternatively, once a user is authenticated, you may access the authenticated user via an `Fluent\Auth\Facades\Auth` instance. Remember, you may gain convenient access to the authenticated user from any controller method in your application :
```php
<?php

namespace App\Controllers;

use Fluent\Auth\Facades\Auth;

class FlightController extends BaseController
{
    /**
     * Update the flight information for an existing flight.
     * 
     * @return mixed
     */
    public function update()
    {
        Auth::user();
        // equivalent with helper
        // auth()->user()
    }
}
```
<a name="determining-if-the-current-user-is-authenticated"></a>
#### Determining If The Current User Is Authenticated

To determine if the user making the incoming HTTP request is authenticated, you may use the `check` method on the `Auth` facade. This method will return `true` if the user is authenticated:
```php
use Fluent\Auth\Facades\Auth;

if (Auth::check()) {
    // The user is logged in...
}
```
> Even though it is possible to determine if a user is authenticated using the `check` method, you will typically use a filter to verify that the user is authenticated before allowing the user access to certain routes / controllers. To learn more about this, check out the documentation on [protecting routes](#protecting-routes).

<a name="protecting-routes"></a>
### Protecting Routes

[Route filter](https://codeigniter4.github.io/userguide/incoming/filters.html) can be used to only allow authenticated users to access a given route, codeigniter4-authentication ships with an `auth` filter, which references the `Fluent\Auth\Filters\AuthenticationFilter` class. You need register this filter with [aliases](https://codeigniter4.github.io/userguide/incoming/filters.html#aliases) `auth` in your application's `app\Config\Filters` config, after register all you need to do is attach the filter to a route definition:
```php
$routes->get('/flights', 'Home::index', ['filter' => 'auth']);
```

<a name="specifying-a-guard"></a>
#### Specifying A Guard

When attaching the `auth` filter to a route, you may also specify which "guard" should be used to authenticate the user. The guard specified should correspond to one of the keys in the `guards` array of your `app\Config\Auth.php` configuration file:
```php
$routes->get('/flights', 'Home::index', ['filter' => 'auth:web']);
```

<a name="authenticating-users"></a>
## Manually Authenticating Users

You are not required to use the authentication scaffolding included with [application starter kits](#install-a-starter-kit). If you choose to not use this scaffolding, you will need to manage user authentication using the codeigniter4-authentication classes directly.

We will access codeigniter4-authentication services via the `Auth` [services](https://codeigniter4.github.io/userguide/concepts/services.html), so we'll need to make sure to import the `Auth` service at the top of the class. Next, let's check out the `attempt` method. The `attempt` method is normally used to handle authentication attempt's from your application's "login" form:
```php
<?php

namespace App\Controllers;

use Fluent\Auth\Config\Services;

class LoginController extends BaseController
{
    /**
     * Handle an authentication attempt.
     *
     * @return \CodeIgniter\Http\RedirectResponse
     */
    public function authenticate()
    {
        $credentials = [
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password')
        ];

        if (Services::auth()->attempt($credentials)) {
            return redirect()->route('dashboard');
        }

        return redirect()->back()->with('error', 'The provided credentials do not match our records.');
    }
}
```
The `attempt` method accepts an array of key / value pairs as its first argument. The values in the array will be used to find the user in your database table. So, in the example above, the user will be retrieved by the value of the `email` column. If the user is found, the hashed password stored in the database will be compared with the `password` value passed to the method via the array. You should not hash the incoming request's `password` value, since the codeigniter4-authentication will automatically hash the value before comparing it to the hashed password in the database. If the two hashed passwords match an authenticated session will be started for the user.

Remember, codeigniter4-authentication services will retrieve users from your database based on your authentication guard's "provider" configuration. In the default `app/Config/Auth.php` configuration file, the Model user provider is specified and it is instructed to use the `App\Models\UserModel` model when retrieving users. You may change these values within your configuration file based on the needs of your application.

The `attempt` method will return `true` if authentication was successful. Otherwise, `false` will be returned.

<a name="specifying-additional-conditions"></a>
#### Specifying Additional Conditions

If you wish, you may also add extra query conditions to the authentication query in addition to the user's email and password. To accomplish this, we may simply add the query conditions to the array passed to the `attempt` method. For example, we may verify that the user is marked as "active":
```php
if (Services::auth()->attempt(['email' => $email, 'password' => $password, 'active' => 1])) {
    // Authentication was successful...
}
```

> In these examples, `email` is not a required option, it is merely used as an example. You should use whatever column name corresponds to in your database table.

<a name="accessing-specific-guard-instances"></a>
#### Accessing Specific Guard Instances

Via the `Auth` services `guard` method, you may specify which guard instance you would like to utilize when authenticating the user. This allows you to manage authentication for separate parts of your application using entirely separate authenticatable models or user tables.

The guard name passed to the `guard` method should correspond to one of the guards configured in your `Auth.php` configuration file:
```php
if (Services::auth()->guard('admin')->attempt($credentials)) {
    // ...
}
```

<a name="remembering-users"></a>
### Remembering Users

Many web applications provide a "remember me" checkbox on their login form. If you would like to provide "remember me" functionality in your application, you may pass a boolean value as the second argument to the `attempt` method.

When this value is `true`, codeigniter4-authentication will keep the user authenticated indefinitely or until they manually logout. Your `users` table must include the string `remember_token` column, which will be used to store the "remember me" token. The `users` table migration on codeigniter4-authentication already includes this column:

```php
use FLuent\Auth\Facades\Auth;

if (Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
   return redirect()->route('dashboard')->withCookies();
}
```

<a name="other-authentication-methods"></a>
### Other Authentication Methods

<a name="authenticate-a-user-instance"></a>
#### Authenticate A User Instance

If you need to set an existing user instance as the currently authenticated user, you may pass the user instance to the `Auth` facade's `login` method. The given user instance must be an implementation of the `Fluent\Auth\Contracts\AuthenticatorInterface`. The `App\Models\UserModel` model included with codeigniter4-authentication already implements this interface. This method of authentication is useful when you already have a valid user instance, such as directly after a user registers with your application:
```php
use Fluent\Auth\Facades\Auth;

Auth::login($user);
```

You may pass a boolean value as the second argument to the `login` method. This value indicates if "remember me" functionality is desired for the authenticated session. Remember, this means that the session will be authenticated indefinitely or until the user manually logs out of the application:
```php
 Auth::login($user, $remember = true);
```
If needed, you may specify an authentication guard before calling the `login` method:
```php
Auth::guard('admin')->login($user);
```

<a name="authenticate-a-user-by-id"></a>
#### Authenticate A User By ID

To authenticate a user using their database record's primary key, you may use the `loginById` method. This method accepts the primary key of the user you wish to authenticate:
```php
Auth::loginById(1);
```

You may pass a boolean value as the second argument to the `loginById` method. This value indicates if "remember me" functionality is desired for the authenticated session. Remember, this means that the session will be authenticated indefinitely or until the user manually logs out of the application:
```php
Auth::loginById(1, $remember = true);
```

## Logging Out

To manually log users out of your application, you may use the `logout` method provided by the `Auth` facade. This will remove the authentication information from the user's session so that subsequent requests are not authenticated.

In addition to calling the `logout` method, it is recommended that you invalidate the user's session. After logging the user out, you would typically redirect the user to the root of your application:
```php
<?php

use Fluent\Auth\Facades\Auth;

/**
 * Log the user out of the application.
 *
 * @return \CodeIgniter\Http\RedirectResponse
 */
public function logout()
{
    Auth::logout();

    return redirect('/')->withCookies();
}
```

<a name="password-confirmation-configuration"></a>
### Configuration

After confirming their password, a user will not be asked to confirm their password again for three hours. However, you may configure the length of time before the user is re-prompted for their password by changing the value of the `$passwordTimeout` configuration value within your application's `app/Config/Auth.php` configuration file.

<a name="password-confirmation-routing"></a>
### Routing

<a name="the-password-confirmation-form"></a>
#### The Password Confirmation Form

First, we will define a route to display a view that requests that the user confirm their password:
```php
$routes->get('confirm-password', 'ConfirmPassword::new', ['filter' => 'auth']);
```

In addition, feel free to include text within the view that explains that the user is entering a protected area of the application and must confirm their password.

<a name="confirming-the-password"></a>
#### Confirming The Password

Next, we will define a route that will handle the form request from the "confirm password" view. This route will be responsible for validating the password and redirecting the user to their intended destination:
```php
use Fluent\Auth\Facades\Hash;

$routes->group('confirm-password', ['as' => 'password.confirm', 'filter' => 'auth'], function() {
    $routes->post('/', function () {
        $request = Services::request();

        if (! Hash::check($request->getPost('password'), auth()->user()->password)) {
            return redirect()->back()->with('error', 'The provided password does not match our records.');
        }

        session()->save('password_confirmed_at', time());

        // example redirect action
        return redirect()->to('action');
    });
});
```

<a name="password-confirmation-protecting-routes"></a>
### Protecting Routes

You should ensure that any route that performs an action which requires recent password confirmation is assigned the `confirm` filter. This filter is included with the default installation of codeigniter4-authentication and will automatically store the user's intended destination in the session so that the user may be redirected to that location after confirming their password. After storing the user's intended destination in the session, the fillter will redirect the user to the `password.confirm` named route:
```php
$routes->group('settings', ['filter' => 'confirm'], function () {
    $routes->get('do-action', function() {
        return 'granted action!';
    });
});
```

<a name="adding-custom-guards"></a>
## Adding Custom Guards

You may define your own authentication guards using the `extend` method on the `Auth` facade or service. You should place your call to the `extend` method within a service provider. Since codeigniter4-authentication already ships with an AuthServiceProvider, we can place the code in that provider. Open `\App\Providers\AuthServiceProvider`:
```php

namespace App\Providers;

use App\Providers\ExampleAdapter;
use Fluent\Auth\Facades\Auth;
use Fluent\Auth\AbstractServiceProvider;

class AuthServiceProvider extends AbstractServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public static function register()
    {
        // You're free to register any flow codeigniter4-authentication,
        // in this below example we're register custom guard.
        // If you wish you can also register provider,
        // hash implementation, password broker etc.

        // Example adding guard implementation.
        Auth::extend('exampleGuard', function($auth, $name, array $config) {
            return new ExampleAdapter(
                $auth, $name, Auth::createUserProvider($config['provider'])
            );
        });
    }
}
```

As you can see in the example above, the callback passed to the `extend` method should return an implementation of `Fluent\Auth\Contracts\AuthenticationInterface`. This interface contains a few methods you will need to implement to define a custom guard. Once your custom guard has been defined, you may reference the guard in the guards configuration of your `Auth.php` configuration file:
```php
public $guards = [
    // ..
    'example' => [
        'driver'   => 'exampleGuard',
        'provider' => 'users',
    ],
],
```

Next we need to register this `App\Providers\AuthServiceProvider` to lifecycle application. Open `App\Config\Events` add this line:
```php
Events::on('pre_system', [\App\Providers\AuthServiceProvider::class, 'register']);
```

<a name="the-user-provider-contract"></a>
### The User Provider Contract

`Fluent\Auth\Contracts\UserProviderInterface` implementations are responsible for fetching an `Fluent\Auth\Contracts\AuthenticatorInterface` implementation out of a persistent storage system, such as MySQL, MongoDB, etc. These two interfaces allow the codeigniter4-authentication mechanisms to continue functioning regardless of how the user data is stored or what type of class is used to represent the authenticated user:

Let's take a look at the `Fluent\Auth\Contracts\UserProviderInterface` contract:
```php
<?php

namespace Fluent\Auth\Contracts;

interface UserProviderInterface
{
    public function findById(int $id);
    public function findByRememberToken(int $id, $token);
    public function updateRememberToken(AuthenticatorInterface $user, $token);
    public function findByCredentials(array $credentials);
    public function validateCredentials(AuthenticatorInterface $user, array $credentials);
}
```

The `findById` function typically receives a key representing the user, such as an auto-incrementing ID from a MySQL database. The `AuthenticatorInterface` implementation matching the ID should be retrieved and returned by the method.

The `findByRememberToken` function retrieves a user by their unique `$id` and "remember me" `$token`, typically stored in a database column like `remember_token`. As with the previous method, the `AuthenticatorInterface` implementation with a matching token value should be returned by this method.

The `updateRememberToken` method updates the `$user` instance's `remember_token` with the new `$token`. A fresh token is assigned to users on a successful "remember me" authentication attempt or when the user is logging out.

The `findByCredentials` method receives the array of credentials passed to the `Auth::attempt` method when attempting to authenticate with an application. The method should then "query" the underlying persistent storage for the user matching those credentials. Typically, this method will run a query with a "where" condition that searches for a user record with a "username" matching the value of `$credentials['username']`. The method should return an implementation of `AuthenticatorInterface`. **This method should not attempt to do any password validation or authentication.**

The `validateCredentials` method should compare the given `$user` with the `$credentials` to authenticate the user. For example, this method will typically use the `Hash::check` method to compare the value of `$user->getAuthPassword()` to the value of `$credentials['password']`. This method should return `true` or `false` indicating whether the password is valid.

<a name="the-authenticatable-contract"></a>
### The Authenticatable Contract

Now that we have explored each of the methods on the `UserProviderInterface`, let's take a look at the `AuthenticatorInterface` contract. Remember, user providers should return implementations of this interface from the `findById`, `findByRememberToken`, and `findByCredentials` methods:
```php
<?php

namespace Fluent\Auth\Contracts;

interface AuthenticatorInterface
{
    public function getAuthIdColumn();
    public function getAuthId();
    public function getAuthPassword();
    public function getRememberToken();
    public function setRememberToken(string $value);
    public function getRememberColumn();
    // and many more
}
```

This interface is simple. The `getAuthIdColumn` method should return the name of the "primary key" field of the user and the `getAuthId` method should return the "primary key" of the user. When using a MySQL back-end, this would likely be the auto-incrementing primary key assigned to the user record. The `getAuthPassword` method should return the user's hashed password.

This interface allows the authentication system to work with any "user" class, regardless of what Model or storage abstraction layer you are using. By default, codeigniter4-authentication includes a `app\Models\UserModel` class in the `app/Models` directory which implements this interface.

