# Laravel FormRequest Tester

A Simple collection of test helpers that help testing form request the easy way.

## Why Bother

for full story on why this package was built please refer to [This Blog Post](https://mohammedmanssour.me/blog/testing-laravel-form-request/)

## Installation

1. Using composer

```
composer require --dev mohammedmanssour/form-request-tester
```

2. add `MohammedManssour\FormRequestTester\TestsFormRequests` trait to your test case.

## Testing a form request

1. you need to initialize the form request using `formRequest` method, it takes the FormRequest class as first argument and an array of request data as a second argument

```php
$this->formRequest(UpdatePost::class, [
    'title' => 'New Title',
    'content' => 'Some Content here',
]);
```

the previous code will initialize the request with `post` method and `/fake-route` if you want to change these options you can via the options array that can be set as a third argument

```php
$this->formRequest(UpdatePost::class, [
    'title' => 'New Title',
    'content' => 'Some Content here',
], [
    'method' => 'put',
    'route' => 'posts/{post}',
]);
```

if you're using `$this->route` method in your form request or other related methods, then your form request won't be authorized unless you set the right http method and route via the `$options` array in order to get the right value for `$this->route` method

2. use the available assertions to test for request

### Available Assertions

|                                                    |                                                                                                               |
| -------------------------------------------------- | ------------------------------------------------------------------------------------------------------------- |
| `$this->assertValidationPassed()`                  | To make sure the validation have passed successfully with the help of the provided data                       |
| `$this->assertValidationFailed()`                  | To make sure the validation have failed with the help of the provided data                                    |
| `$this->assertValidationErrors($keysArray)`        | To assert that the keys mentioned in the `$keysArray` have occurred in the errors bag.                        |
| `$this->assertValidationErrorsMissing($keysArray)` | To assert that the keys mentioned in the `$keysArray` have not occurred in the errors bag.                    |
| `$this->assertValidationMessages($messagesArray)`  | To assert that the messages exists in the error bag. Used when you define custom messages for your validation |
| `$this->assertAuthorized()`                        | To assert that request have been authorized via the form request                                              |
| `$this->assertNotAuthorized()`                     | To assert that request have not been authorized via the form request                                          |

### Example Usage:

Taking into consideration:

1. title & content are required field,
2. **Content field is required** is a custom error message used for content field
3. `$this->route` method is used in authorize method
4. `Route::put('posts/{post}', 'PostsController@update')` is the route used to update a post

```php
$this->formRequest(UpdatePost::class, [
    'title' => 'New Title',
], [
    'method' => 'put',
    'route' => 'posts/{post}',
])->assertAuthorized()
    ->assertValidationFailed()
    ->assertValidationErrors(['content'])
    ->assertValidationErrorsMissing(['title'])
    ->assertValidationMessages(['Content field is required'])
```

## Contributors:

1. [Mohammed Manssour](https://mohammedmanssour.me)
