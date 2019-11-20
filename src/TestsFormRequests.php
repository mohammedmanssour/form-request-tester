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

/**
 * TODO: add assertions to assert redirections
 */
trait TestsFormRequests
{
    /**
     * the current form request that needs to be tested
     * @var \Illuminate\Foundation\Http\FormRequest
     */
    public $currentFormRequest;

    /**
     * validation errors
     * null when validation haven't been done yet
     *
     * @var array
     */
    public $errors = null;

    /**
     * the form requests authorization status
     *
     * @var boolean
     */
    public $formRequestAuthorized = true;

    /*----------------------------------------------------
     * Initialize functions
    --------------------------------------------------- */



    /**
     * make form request with
     *
     * @param string $formRequestType
     * @param array $data default to empty
     * @param array $options ['route' => 'Route to instantiate form request with', 'method' => 'to instantiate form request with']
     * @return $this
     */
    public function formRequest(string $formRequestType, $data = [], $options = [])
    {
        $options = array_merge([
            'method' => 'post',
            'route' => '/fake-route'
        ], $options);

        $this->currentFormRequest =
            $formRequestType::create($options['route'], $options['method'], $data)
            ->setContainer($this->app)
            ->setRedirector($this->makeRequestRedirector());

        $this->currentFormRequest->setRouteResolver(function () {
            $routes = Route::getRoutes();
            $route = null;
            try {
                $route = $routes->match($this->currentFormRequest);

                // Substitute Bindings
                $router = app()->make(Registrar::class);
                $router->substituteBindings($route);
                $router->substituteImplicitBindings($route);
            } catch (\Exception $e) {
            }
            finally {
                return $route;
            }
        });

        $this->validateFormRequest();

        return $this;
    }

    /**
     * validates form request and save the errors
     *
     * @return void
     */
    public function validateFormRequest()
    {
        try {
            $this->currentFormRequest->validateResolved();
        } catch (ValidationException $e) {
            $this->errors = $e->errors();
        } catch (AuthorizationException $e) {
            $this->formRequestAuthorized = false;
        }
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
        if (!$this->formRequestAuthorized) {
            Assert::fail('Form request is not authorized');
        }

        if (!empty($this->errors)) {
            Assert::fail('Validation have failed');
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
        if (!$this->formRequestAuthorized) {
            Assert::fail('Form request is not authorized');
        }

        foreach (Arr::wrap($keys) as $key) {
            $this->assertTrue(
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
        if (!$this->formRequestAuthorized) {
            Assert::fail('Form request is not authorized');
        }

        foreach (Arr::wrap($keys) as $key) {
            $this->assertTrue(
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
        $errors = Arr::flatten(Arr::wrap($this->errors));
        foreach ($messages as $message) {
            $this->assertContains(
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
        $this->assertTrue($this->formRequestAuthorized, "Form Request was not authorized");
        return $this;
    }

    /**
     * assert that the current user was not authorized by the form request
     *
     * @return $this
     */
    public function assertNotAuthorized()
    {
        $this->assertFalse($this->formRequestAuthorized, "Form Request was authorized");
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
        $this->assertTrue(true, $message);
    }
}