# Authorization

- [Introduction](#introduction)
- [Gates](#gates)
    - [Writing Gates](#writing-gates)
    - [Authorizing Actions](#authorizing-actions-via-gates)
    - [Gate Responses](#gate-responses)
    - [Intercepting Gate Checks](#intercepting-gate-checks)
    - [Inline Authorization](#inline-authorization)
- [Creating Policies](#creating-policies)
    - [Generating Policies](#generating-policies)
    - [Registering Policies](#registering-policies)
- [Writing Policies](#writing-policies)
    - [Policy Methods](#policy-methods)
    - [Policy Responses](#policy-responses)
    - [Methods Without Models](#methods-without-models)
    - [Guest Users](#guest-users)
    - [Policy Filters](#policy-filters)
- [Authorizing Actions Using Policies](#authorizing-actions-using-policies)
    - [Via The User Model](#via-the-user-model)
    - [Via Controller Helpers](#via-controller-helpers)
    - [Via Filter](#via-filters)
    - [Supplying Additional Context](#supplying-additional-context)

<a name="introduction"></a>
## Introduction

In addition to providing built-in [authentication](/docs/en/authentication) services, it's also provides a simple way to authorize user actions against a given resource. For example, even though a user is authenticated, they may not be authorized to update or delete certain models or database records managed by your application. This authorization features provide an easy, organized way of managing these types of authorization checks.

CodeIgniter4 Authentication provides two primary ways of authorizing actions: [gates](#gates) and [policies](#creating-policies). Think of gates and policies like routes and controllers. Gates provide a simple, closure-based approach to authorization while policies, like controllers, group logic around a particular model or resource. In this documentation, we'll explore gates first and then examine policies.

You do not need to choose between exclusively using gates or exclusively using policies when building an application. Most applications will most likely contain some mixture of gates and policies, and that is perfectly fine! Gates are most applicable to actions which are not related to any model or resource, such as viewing an administrator dashboard. In contrast, policies should be used when you wish to authorize an action for a particular model or resource.

<a name="gates"></a>
## Gates

<a name="writing-gates"></a>
### Writing Gates

> Gates are a great way to learn the basics of authorization features; however, when building robust applications you should consider using [policies](#creating-policies) to organize your authorization rules.

Gates are simply closures that determine if a user is authorized to perform a given action. Typically, gates are defined within the `register` method of the `App\Providers\AuthServiceProvider` class using the `Gate` facade. Gates always receive a user instance as their first argument and may optionally receive additional arguments such as a relevant entities or model.

In this example, we'll define a gate to determine if a user can update a given `App\Models\Post` model. The gate will accomplish this by comparing the user's `id` against the `user_id` of the user that created the post:
```php
use App\Entities\Post;
use App\Entities\User;
use Fluent\Auth\Facades\Gate;

/**
 * Register any authentication / authorization services.
 *
 * @return void
 */
public static function register()
{
    $this->registerPolicies();

    Gate::define('update-post', function (User $user, Post $post) {
        return $user->id === $post->user_id;
    });
}
```

Like controllers, gates may also be defined using a class callback array:
```php
use App\Policies\PostPolicy;
use Fluent\Auth\Facades\Gate;

/**
 * Register any authentication / authorization services.
 *
 * @return void
 */
public static function register()
{
    $this->registerPolicies();

    Gate::define('update-post', [PostPolicy::class, 'update']);
}
```

<a name="authorizing-actions-via-gates"></a>
### Authorizing Actions

To authorize an action using gates, you should use the `allows` or `denies` methods provided by the `Gate` facade. Note that you are not required to pass the currently authenticated user to these methods. Will automatically take care of passing the user into the gate closure. It is typical to call the gate authorization methods within your application's controllers before performing an action that requires authorization:
```php
namespace App\Controllers;

use App\Models\Post;
use Fluent\Auth\Facades\Gate;

class PostController extends BaseController
{
    /**
     * Update the given post.
     */
    public function update()
    {
        $post = (new Post())->find(1);

        if (! Gate::allows('update-post', $post)) {
            return $this->response->setStatusCode(403);
        }

        // Update the post...
    }
}
```

If you would like to determine if a user other than the currently authenticated user is authorized to perform an action, you may use the `forUser` method on the `Gate` facade:
```php
if (Gate::forUser($user)->allows('update-post', $post)) {
    // The user can update the post...
}

if (Gate::forUser($user)->denies('update-post', $post)) {
    // The user can't update the post...
}
```

You may authorize multiple actions at a time using the `any` or `none` methods:
```php
if (Gate::any(['update-post', 'delete-post'], $post)) {
    // The user can update or delete the post...
}

if (Gate::none(['update-post', 'delete-post'], $post)) {
    // The user can't update or delete the post...
}
```

<a name="authorizing-or-throwing-exceptions"></a>
#### Authorizing Or Throwing Exceptions

If you would like to attempt to authorize an action and automatically throw an `Fluent\Auth\Exceptions\AuthorizationException` if the user is not allowed to perform the given action, you may use the `Gate` facade's `authorize` method. Instances of `AuthorizationException` are automatically converted to a 403 HTTP response:
```php
Gate::authorize('update-post', $post);

// The action is authorized...
```

<a name="gates-supplying-additional-context"></a>
#### Supplying Additional Context

The gate methods for authorizing abilities (`allows`, `denies`, `check`, `any`, `none`, `authorize`, `can`, `cannot`). These array elements are passed as parameters to the gate closure, and can be used for additional context when making authorization decisions:
```php
use App\Entities\Category;
use App\Entities\User;
use Fluent\Auth\Facades\Gate;

Gate::define('create-post', function (User $user, Category $category, $pinned) {
    if (! $user->canPublishToGroup($category->group)) {
        return false;
    } elseif ($pinned && ! $user->canPinPosts()) {
        return false;
    }

    return true;
});

if (Gate::check('create-post', [$category, $pinned])) {
    // The user can create the post...
}
```

<a name="gate-responses"></a>
### Gate Responses

So far, we have only examined gates that return simple boolean values. However, sometimes you may wish to return a more detailed response, including an error message. To do so, you may return an `Fluent\Auth\Authorization\Response` from your gate:
```php
use App\Entities\User;
use Fluent\Auth\Authorization\Response;
use Fluent\Auth\Facades\Gate;

Gate::define('edit-settings', function (User $user) {
    return $user->isAdmin
        ? Response::allow()
        : Response::deny('You must be an administrator.');
});
```

Even when you return an authorization response from your gate, the `Gate::allows` method will still return a simple boolean value; however, you may use the `Gate::inspect` method to get the full authorization response returned by the gate:
```php
$response = Gate::inspect('edit-settings');

if ($response->allowed()) {
    // The action is authorized...
} else {
    echo $response->message();
}
```

When using the `Gate::authorize` method, which throws an `AuthorizationException` if the action is not authorized, the error message provided by the authorization response will be propagated to the HTTP response:
```php
Gate::authorize('edit-settings');

// The action is authorized...
```

<a name="intercepting-gate-checks"></a>
### Intercepting Gate Checks

Sometimes, you may wish to grant all abilities to a specific user. You may use the `before` method to define a closure that is run before all other authorization checks:
```php
use Fluent\Auth\Facades\Gate;

Gate::before(function ($user, $ability) {
    if ($user->isAdministrator()) {
        return true;
    }
});
```

If the `before` closure returns a non-null result that result will be considered the result of the authorization check.

You may use the `after` method to define a closure to be executed after all other authorization checks:
```php
Gate::after(function ($user, $ability, $result, $arguments) {
    if ($user->isAdministrator()) {
        return true;
    }
});
```

Similar to the `before` method, if the `after` closure returns a non-null result that result will be considered the result of the authorization check.

<a name="inline-authorization"></a>
### Inline Authorization

Occasionally, you may wish to determine if the currently authenticated user is authorized to perform a given action without writing a dedicate gate that corresponds to the action, allows you to perform these types of "inline" authorization checks via the `Gate::allowIf` and `Gate::denyIf` methods:

```php
use Fluent\Auth\Facades\Auth;

Gate::allowIf(fn ($user) => $user->isAdministrator());

Gate::denyIf(fn ($user) => $user->banned());
```

If the action is not authorized or if no user is currently authenticated, will automatically throw an `Fluent\Auth\Authorization\AuthorizationException` exception. Instances of `AuthorizationException` are automatically converted to a 403 HTTP response.

<a name="creating-policies"></a>
## Creating Policies

<a name="generating-policies"></a>
### Generating Policies

Policies are classes that organize authorization logic around a particular model or resource. For example, if your application is a blog, you may have a `App\Models\Post` model and a corresponding `App\Policies\PostPolicy` to authorize user actions such as creating or updating posts.

<a name="registering-policies"></a>
### Registering Policies

Once the policy class has been created, it needs to be registered. Registering policies is how we can inform which policy to use when authorizing actions against a given model type.

The `App\Providers\AuthServiceProvider` included with fresh installation codeigniter4-authentication contains a `policies` property which maps your Entities models to their corresponding policies. Registering a policy will instruct which policy to utilize when authorizing actions against a given entities model:
```php
namespace App\Providers;

use App\Entities\Post;
use App\Policies\PostPolicy;
use Fluent\Auth\AbstractServiceProvider;
use Fluent\Auth\Facades\Gate;

class AuthServiceProvider extends AbstractServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected static $policies = [
        Post::class => PostPolicy::class,
    ];

    /**
     * Register any application authentication / authorization services.
     *
     * @return void
     */
    public static function register()
    {
        static::registerPolicies();
        //
    }
}
```

<a name="policy-auto-discovery"></a>
#### Policy Auto-Discovery

Instead of manually registering model policies, can automatically discover policies as long as the model and policy follow standard naming conventions. Specifically, the policies must be in a `Policies` directory at or above the directory that contains your models. So, for example, the models may be placed in the `app/Models` directory while the policies may be placed in the `app/Policies` directory. In this situation, will check for policies in `app/Models/Policies` then `app/Policies`. In addition, the policy name must match the model name and have a `Policy` suffix. So, a `User` model would correspond to a `UserPolicy` policy class.

If you would like to define your own policy discovery logic, you may register a custom policy discovery callback using the `Gate::guessPolicyNamesUsing` method. Typically, this method should be called from the `register` method of your application's `AuthServiceProvider`:
```php
use Fluent\Auth\Facades\Gate;

Gate::guessPolicyNamesUsing(function ($modelClass) {
    // Return the name of the policy class for the given model...
});
```

> Any policies that are explicitly mapped in your `AuthServiceProvider` will take precedence over any potentially auto-discovered policies.

<a name="writing-policies"></a>
## Writing Policies

<a name="policy-methods"></a>
### Policy Methods

Once the policy class has been registered, you may add methods for each action it authorizes. For example, let's define an `update` method on our `PostPolicy` which determines if a given `App\Models\User` can update a given `App\Models\Post` instance.

The `update` method will receive a `User` and a `Post` instance as its arguments, and should return `true` or `false` indicating whether the user is authorized to update the given `Post`. So, in this example, we will verify that the user's `id` matches the `user_id` on the post:
```php
namespace App\Policies;

use App\Entities\Post;
use App\Entities\User;

class PostPolicy
{
    /**
     * Determine if the given post can be updated by the user.
     *
     * @param  \App\Entities\User  $user
     * @param  \App\Entities\Post  $post
     * @return bool
     */
    public function update(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }
}
```

You may continue to define additional methods on the policy as needed for the various actions it authorizes. For example, you might define `viewAny`, `view`, `create`, `update`, `delete`, `restore`, and `forceDelete` actions methods to authorize various `Post` related actions, but remember you are free to give your policy methods any name you like.

<a name="policy-responses"></a>
### Policy Responses

So far, we have only examined policy methods that return simple boolean values. However, sometimes you may wish to return a more detailed response, including an error message. To do so, you may return an `Fluent\Auth\Authorization\Response` instance from your policy method:
```php
use App\Entities\Post;
use App\Entities\User;
use Fluent\Auth\Authorization\Response;

/**
 * Determine if the given post can be updated by the user.
 *
 * @param  \App\Entities\User  $user
 * @param  \App\Entities\Post  $post
 * @return \Fluent\Auth\Authorization\Response
 */
public function update(User $user, Post $post)
{
    return $user->id === $post->user_id
        ? Response::allow()
        : Response::deny('You do not own this post.');
}
```

When returning an authorization response from your policy, the `Gate::allows` method will still return a simple boolean value; however, you may use the `Gate::inspect` method to get the full authorization response returned by the gate:
```php
use Fluent\Auth\Facades\Gate;

$response = Gate::inspect('update', $post);

if ($response->allowed()) {
    // The action is authorized...
} else {
    echo $response->message();
}
```

When using the `Gate::authorize` method, which throws an `AuthorizationException` if the action is not authorized, the error message provided by the authorization response will be propagated to the HTTP response:
```php
Gate::authorize('update', $post);

// The action is authorized...
```

<a name="methods-without-models"></a>
### Methods Without Models

Some policy methods only receive an instance of the currently authenticated user. This situation is most common when authorizing `create` actions. For example, if you are creating a blog, you may wish to determine if a user is authorized to create any posts at all. In these situations, your policy method should only expect to receive a user instance:
```php
/**
 * Determine if the given user can create posts.
 *
 * @param  \App\Entities\User  $user
 * @return bool
 */
public function create(User $user)
{
    return $user->role == 'writer';
}
```

<a name="guest-users"></a>
### Guest Users

By default, all gates and policies automatically return `false` if the incoming HTTP request was not initiated by an authenticated user. However, you may allow these authorization checks to pass through to your gates and policies by declaring an "optional" type-hint or supplying a `null` default value for the user argument definition:
```php
namespace App\Policies;

use App\Entities\Post;
use App\Entities\User;

class PostPolicy
{
    /**
     * Determine if the given post can be updated by the user.
     *
     * @param  \App\Entities\User  $user
     * @param  \App\Entities\Post  $post
     * @return bool
     */
    public function update(?User $user, Post $post)
    {
        return optional($user)->id === $post->user_id;
    }
}
```

<a name="policy-filters"></a>
### Policy Filters

For certain users, you may wish to authorize all actions within a given policy. To accomplish this, define a `before` method on the policy. The `before` method will be executed before any other methods on the policy, giving you an opportunity to authorize the action before the intended policy method is actually called. This feature is most commonly used for authorizing application administrators to perform any action:
```php
use App\Entities\User;

/**
 * Perform pre-authorization checks.
 *
 * @param  \App\Entities\User  $user
 * @param  string  $ability
 * @return void|bool
 */
public function before(User $user, $ability)
{
    if ($user->isAdministrator()) {
        return true;
    }
}
```

If you would like to deny all authorization checks for a particular type of user then you may return `false` from the `before` method. If `null` is returned, the authorization check will fall through to the policy method.

> The `before` method of a policy class will not be called if the class doesn't contain a method with a name matching the name of the ability being checked.

<a name="authorizing-actions-using-policies"></a>
## Authorizing Actions Using Policies

<a name="via-the-user-model"></a>
### Via The User Model

The `App\Models\User` model that is included with your application includes two helpful methods for authorizing actions: `can` and `cannot`. The `can` and `cannot` methods receive the name of the action you wish to authorize and the relevant model. For example, let's determine if a user is authorized to update a given `App\Models\Post` model. Typically, this will be done within a controller method:
```php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Post;

class PostController extends BaseController
{
    /**
     * Update the given post.
     */
    public function update($id)
    {
        $post = (new Post())->find($id);

        if (auth()->user()->cannot('update', $post)) {
            return $this->response->setStatusCode(403);
        }
        // Update the post...
    }
}
```

If a [policy is registered](#registering-policies) for the given model, the `can` method will automatically call the appropriate policy and return the boolean result. If no policy is registered for the model, the `can` method will attempt to call the closure-based Gate matching the given action name.

<a name="user-model-actions-that-dont-require-models"></a>
#### Actions That Don't Require Models

Remember, some actions may correspond to policy methods like `create` that do not require a model instance. In these situations, you may pass a class name to the `can` method. The class name will be used to determine which policy to use when authorizing the action:
```php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Entities\Post;

class PostController extends BaseController
{
    /**
     * Create a post.
     */
    public function store()
    {
        if (auth()->user()->cannot('create', Post::class)) {
            return $this->response->setStatusCode(403);
        }
        // Create the post...
    }
}
```

<a name="via-controller-helpers"></a>
### Via Controller Helpers

In addition to helpful methods provided to the `App\Models\User` model, provides a helpful `authorize` method to any of your controllers must use trait of `Fluent\Auth\Traits\AuthorizesRequestsTrait`.

Like the `can` method, this method accepts the name of the action you wish to authorize and the relevant model. If the action is not authorized, the `authorize` method will throw an `Fluent\Auth\Authorization\AuthorizationException` exception which the exception handler will automatically convert to an HTTP response with a 403 status code:
```php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\Post;
use Flunet\Auth\Traits\AuthorizesRequestsTrait;

class PostController extends BaseController
{
    use AuthorizesRequestsTrait;

    /**
     * Update the given blog post.
     *
     * @throws \Fluent\Auth\Authorization\AuthorizationException
     */
    public function update($id)
    {
        $post = (new Post())->find($id);

        $this->authorize('update', $post);
        // The current user can update the blog post...
    }
}
```

<a name="controller-actions-that-dont-require-models"></a>
#### Actions That Don't Require Models

As previously discussed, some policy methods like `create` do not require a model instance. In these situations, you should pass a class name to the `authorize` method. The class name will be used to determine which policy to use when authorizing the action:
```php
use App\Entities\Post;

/**
 * Create a new blog post.
 *
 * @throws \Fluent\Auth\Authorization\AuthorizationException
 */
public function create()
{
    $this->authorize('create', Post::class);
    // The current user can create blog posts...
}
```

<a name="via-filters"></a>
### Via Filters

CodeIgniter4 Authentication includes a filter that can authorize actions before the incoming request. By default, the `Fluent\Auth\Filters\AuthorizeFilter` filter is assigned the `can` alias key in your `app\Config\Filters` class. Let's explore an example of using the `can` filter to authorize that a user can update a post:
```php
$routes->put('post/(:any)', 'PostController::update/$1', ['filter' => 'can:update']);
```

In this example, we're passing the `can` filter with arguments. The first is the name of the action we wish to authorize we wish to pass to the policy method.

<a name="middleware-actions-that-dont-require-models"></a>
#### Actions That Don't Require Models

Again, some policy methods like `create` do not require a model instance. In these situations, you may pass a class name to the middleware. The class name will be used to determine which policy to use when authorizing the action:
```php
$routes->post('post', 'PostController::create', ['filter' => 'can:create,App\Entities\Post']);
```

<a name="supplying-additional-context"></a>
### Supplying Additional Context

When authorizing actions using policies, you may pass an array as the second argument to the various authorization functions and helpers. The first element in the array will be used to determine which policy should be invoked, while the rest of the array elements are passed as parameters to the policy method and can be used for additional context when making authorization decisions. For example, consider the following `PostPolicy` method definition which contains an additional `$category` parameter:
```php
/**
 * Determine if the given post can be updated by the user.
 *
 * @param  \App\Entities\User  $user
 * @param  \App\Entities\Post  $post
 * @param  int  $category
 * @return bool
 */
public function update(User $user, Post $post, int $category)
{
    return $user->id === $post->user_id &&
           $user->canUpdateCategory($category);
}
```

When attempting to determine if the authenticated user can update a given post, we can invoke this policy method like so:
```php
use App\Models\Post;

/**
 * Update the given blog post.
 *
 * @throws \Fluent\Auth\Authorization\AuthorizationException
 */
public function update($id)
{
    $post = (new Post())->find($id);

    $this->authorize('update', [$post, $this->request->getPost('category')]);
    // The current user can update the blog post...
}
```