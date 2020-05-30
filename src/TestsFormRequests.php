<?php

namespace MohammedManssour\FormRequestTester;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    /*----------------------------------------------------
     * Initialize functions
    --------------------------------------------------- */

    /**
     * make form request with
     *
     * @param string $formRequestType
     * @param array $data default to empty
     * @param array $options ['route' => 'Route to instantiate form request with', 'method' => 'to instantiate form request with']
     * @return \MohammedManssour\FormRequestTester\FormRequestTester
     */
    public function formRequest(string $formRequestType, $data = [], $options = [])
    {
        $options = array_merge([
            'method' => 'post',
            'route' => '/fake-route'
        ], $options);

        return (new FormRequestTester($this))
            ->setFormRequest($formRequestType)
            ->method($options['method'], $data)
            ->withRoute($options['route']);
    }

    public function getApp() {
        return $this->app;
    }
}
