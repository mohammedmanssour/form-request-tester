<?php

namespace MohammedManssour\FormRequestTester;

use \Mockery;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Assert;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Routing\Registrar;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

class FormRequestTester
{

    /**
     * laravel test case
     *
     * @var TastCase
     */
    private $test;

    /**
     * current instance of the form request
     *
     * @var \Illuminate\Foundation\Http\FormRequest
     */
    private $currentFormRequest;

    /**
     * form request class
     *
     * @var string
     */
    private $formRequest;

    /**
     * form request method
     * used to make $this->route('') method works out of the box
     *
     * @var string
     */
    private $method;

    /**
     * form request route
     * used to make $this->route('') method works out of the box
     *
     * @var string
     */
    private $route;

    /**
     * key/values pair of route paramters to be user with $this->route('parameter') name
     *
     * @var array
     */
    private $routeParameters = [];

    /**
     * form request data
     *
     * @var array
     */
    private $data;

    /**
     * validation errors
     * null when validation haven't been done yet
     *
     * @var array
     */
    private $errors = null;

    /**
     * the form requests authorization status
     *
     * @var boolean
     */
    private $formRequestAuthorized = true;

    /**
     * validated form request data
     *
     * @var array
     */
    private $validated;

    /**
     * Create new FormRequestTester instance
     *
     * @param \Illuminate\Foundation\Testing\TestCase $test
     */
    public function __construct($test)
    {
        $this->test = $test;
    }

    /*-----------------------------------------------------
     * Setters and getters
     -----------------------------------------------------*/
    /**
     * set FormRequest Class
     *
     * @param string $formRequest
     * @return \MohammedManssour\FormRequestTester\FormRequestTester
     */
    public function setFormRequest($formRequest)
    {
        $this->formRequest = $formRequest;
        return $this;
    }

    /**
     * set FormRequest route
     *
     * @param string $route
     * @return \MohammedManssour\FormRequestTester\FormRequestTester
     */
    public function withRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * add route parameter to be resolved when using $this->route('parameter')
     *
     * @param string $name
     * @param mixed $value
     * @return \MohammedManssour\FormRequestTester\FormRequestTester
     */
    public function addRouteParameter($name, $value)
    {
        $this->routeParameters[$name] = $value;
        return $this;
    }

    /**
     * set Form request method and data
     *
     * @param string $method
     * @param array $data
     * @return \MohammedManssour\FormRequestTester\FormRequestTester
     */
    public function method($method, $data = [])
    {
        $this->method = $method;
        $this->data = $data;
        return $this;
    }

    /**
     * set form request method to get
     *
     * @return \MohammedManssour\FormRequestTester\FormRequestTester
     */
    public function get()
    {
        return $this->method('get', []);
    }

    /**
     * set form request method to post with data
     *
     * @param array $data
     * @return \MohammedManssour\FormRequestTester\FormRequestTester
     */
    public function post($data = [])
    {
        return $this->method('post', $data);
    }

    /**
     * set form request method to put with data
     *
     * @param array $data
     * @return \MohammedManssour\FormRequestTester\FormRequestTester
     */
    public function put($data = [])
    {
        return $this->method('put', $data);
    }

    /**
     * set form request method to delete with data
     *
     * @param array $data
     * @return \MohammedManssour\FormRequestTester\FormRequestTester
     */
    public function delete($data = [])
    {
        return $this->method('delete', $data);
    }

    /**
     * get current form requset
     *
     * @return \Illuminate\Foundation\Http\FormRequest
     */
    public function formRequest()
    {
        return $this->currentFormRequest;
    }

    /*-----------------------------------------------------
    * Form Request specific methods
    -----------------------------------------------------*/
    /**
     * check whether form request is build or not
     *
     * @return void
     */
    public function checkFormRequest()
    {
        if (!is_null($this->currentFormRequest) && !is_null($this->validated)) {
            return;
        }

        if (is_null($this->checkFormRequest())) {
            $this->buildFormRequest();
        }

        $this->validateFormRequest();
    }

    /**
     * build form request
     *
     * @return void
     */
    public function buildFormRequest()
    {
        $this->currentFormRequest =
            $this->formRequest::create($this->getRoute(), $this->method, $this->data)
            ->setContainer($this->test->getApp())
            ->setRedirector($this->makeRequestRedirector());

        $this->currentFormRequest->setRouteResolver(function () {
            $this->registerFakeRouteRule();
            return $this->routeResolver();
        });

        $this->userResolver();
    }

    /**
     * create fake request redirector to be used in request
     *
     * @return \Illuminate\Routing\Redirector
     */
    private function makeRequestRedirector()
    {
        $fakeUrlGenerator = Mockery::mock();
        $fakeUrlGenerator->shouldReceive('to', 'route', 'action', 'previous')->withAnyArgs()->andReturn(null);

        $redirector = Mockery::mock(Redirector::class);
        $redirector->shouldReceive('getUrlGenerator')->andReturn($fakeUrlGenerator);

        return $redirector;
    }

    /**
     * validates form request and save the errors
     *
     * @return void
     */
    private function validateFormRequest()
    {
        try {
            $this->currentFormRequest->validateResolved();
            $this->validated = $this->currentFormRequest->validated();
        } catch (ValidationException $e) {
            $this->errors = $e->errors();
        } catch (AuthorizationException $e) {
            $this->formRequestAuthorized = false;
        }
    }

    /**
     * register Fake Route to be
     *
     * @return void
     */
    private function registerFakeRouteRule()
    {
        if (empty($this->routeParameters)) {
            return null;
        }

        $fakeRoute = collect($this->routeParameters)
            ->keys()
            ->map(fn ($param) => "{{$param}}")
            ->prepend('fake-route')
            ->implode('/');

        Route::{$this->method}($fakeRoute);
    }

    private function getRoute()
    {
        if ($this->route) {
            return $this->route;
        }

        return collect($this->routeParameters)
            ->map(fn ($value) => $value)
            ->prepend('fake-route')
            ->implode('/');
    }

    /**
     * find the routing rule that matches the route provided with withRoute
     *
     * @return void
     */
    private function routeResolver()
    {
        $routes = Route::getRoutes();
        try {
            $route = $routes->match($this->currentFormRequest);

            // Substitute Bindings
            $router = app()->make(Registrar::class);
            $router->substituteBindings($route);
            $router->substituteImplicitBindings($route);

            return $route;
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * add the logic that formRequest needs to resolve user
     *
     * @return void
     */
    public function userResolver()
    {
        $this->currentFormRequest->setUserResolver(function () {
            return auth()->user();
        });
    }

    /*----------------------------------------------------
     * Assertions functions
    --------------------------------------------------- */
    /**
     * assert form request validation have passed
     *
     * @return $this
     */
    public function assertValidationPassed()
    {
        $this->checkFormRequest();

        if (!$this->formRequestAuthorized) {
            Assert::fail('Form request is not authorized');
        }

        if (!empty($this->errors)) {
            Assert::fail('Validation failed: ' . json_encode($this->errors));
            return $this;
        }

        $this->succeed('Validation passed successfully');
        return $this;
    }

    /**
     * assert form request validation have failed
     *
     * @return $this
     */
    public function assertValidationFailed()
    {
        $this->checkFormRequest();

        if (!$this->formRequestAuthorized) {
            Assert::fail('Form request is not authorized');
        }

        if (!empty($this->errors)) {
            $this->succeed('Validation have failed');
            return $this;
        }

        Assert::fail('Validation have passed');
        return $this;
    }

    /**
     * assert the validation errors has the following keys
     *
     * @param array $keys
     * @return $this
     */
    public function assertValidationErrors($keys)
    {
        $this->checkFormRequest();

        if (!$this->formRequestAuthorized) {
            Assert::fail('Form request is not authorized');
        }

        foreach (Arr::wrap($keys) as $key) {
            $this->test->assertTrue(
                isset($this->errors[$key]),
                "Failed to find a validation error for key: '{$key}'"
            );
        }

        return $this;
    }


    /**
     * assert the validation errors doesn't have a key
     *
     * @param array $keys
     * @return $this
     */
    public function assertValidationErrorsMissing($keys)
    {
        $this->checkFormRequest();

        if (!$this->formRequestAuthorized) {
            Assert::fail('Form request is not authorized');
        }

        foreach (Arr::wrap($keys) as $key) {
            $this->test->assertTrue(
                !isset($this->errors[$key]),
                "validation error for key: '{$key}' was found in errors array"
            );
        }

        return $this;
    }

    /**
     * assert that validation has the messages
     *
     * @return $this
     */
    public function assertValidationMessages($messages)
    {
        $this->checkFormRequest();

        $errors = Arr::flatten(Arr::wrap($this->errors));
        foreach ($messages as $message) {
            $this->test->assertContains(
                $message,
                $errors,
                "Failed to find the validation message '${message}' in the validation messages"
            );
        }

        return $this;
    }

    /**
     * assert that the current user was authorized by the form request
     *
     * @return $this
     */
    public function assertAuthorized()
    {
        $this->checkFormRequest();

        $this->test->assertTrue($this->formRequestAuthorized, "Form Request was not authorized");
        return $this;
    }

    /**
     * assert that the current user was not authorized by the form request
     *
     * @return $this
     */
    public function assertNotAuthorized()
    {
        $this->checkFormRequest();

        $this->test->assertFalse($this->formRequestAuthorized, "Form Request was authorized");
        return $this;
    }

    /**
     * assert the success of the current test
     *
     * @param string $message
     * @return void
     */
    public function succeed($message = '')
    {
        $this->test->assertTrue(true, $message);
    }

    /**
     * assert the validation errors has the following keys
     *
     * @param array $keys
     * @return $this
     */
    public function assertValidationData($keys)
    {
        $this->checkFormRequest();

        if (!$this->formRequestAuthorized) {
            Assert::fail('Form request is not authorized');
        }

        foreach (Arr::wrap($keys) as $key) {
            $this->test->assertTrue(
                isset($this->validated[$key]),
                "Failed to find a validation data for key: '{$key}'"
            );
        }

        return $this;
    }

    /**
     * assert the validation data doesn't have a key
     *
     * @param array $keys
     * @return $this
     */
    public function assertValidationDataMissing($keys)
    {
        $this->checkFormRequest();

        if (!$this->formRequestAuthorized) {
            Assert::fail('Form request is not authorized');
        }

        foreach (Arr::wrap($keys) as $key) {
            $this->test->assertTrue(
                !isset($this->validated[$key]),
                "validation error for key: '{$key}' was found in validated array"
            );
        }

        return $this;
    }
}
