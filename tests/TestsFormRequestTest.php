<?php

namespace MohammedManssour\FormRequestTester\Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use MohammedManssour\FormRequestTester\TestsFormRequests;
use MohammedManssour\FormRequestTester\Tests\Stubs\Models\Post;
use MohammedManssour\FormRequestTester\Tests\Stubs\Models\User;
use MohammedManssour\FormRequestTester\Tests\Stubs\FormRequests\UpdatePost;

class TestsFormRequestTest extends TestCase
{
    use
        DatabaseMigrations,
        TestsFormRequests;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
        $this->post = factory(Post::class)->create([
            'user_id' => $this->user->id
        ]);

        $this->actingAs($this->user);
    }

    /** @test */
    public function validation_will_pass()
    {
        $data = [
            'content' => 'This is content',
            'user_id' => $this->user->id
        ];

        $this->formRequest(
            UpdatePost::class,
            $data,
            ['method' => 'put', 'route' => "posts/{$this->post->id}"]
        )->assertValidationPassed();

        // test the ability to build form request using the intuitive methods
        $this->formRequest(UpdatePost::class)
            ->put($data)
            ->withRoute("posts/{$this->post->id}")
            ->assertValidationPassed();
    }

    /** @test */
    public function validation_will_fail()
    {
        $this->formRequest(
            UpdatePost::class,
            [],
            ['method' => 'put', 'route' => "posts/{$this->post->id}"]
        )
            ->assertValidationFailed()
            ->assertValidationErrors(['user_id', 'content'])
            ->assertValidationMessages(['Content Field is required', 'User Field is required']);

        // test the ability to build form request using the intuitive methods
        $this->formRequest(UpdatePost::class)
            ->put()
            ->withRoute("posts/{$this->post->id}")
            ->assertValidationFailed()
            ->assertValidationErrors(['user_id', 'content'])
            ->assertValidationMessages(['Content Field is required', 'User Field is required']);
    }

    /** @test */
    public function validation_will_fail_because_user_id_is_not_valid()
    {
        $data = [
            'content' => 'This is content',
            'user_id' => 2000
        ];

        $this->formRequest(
            UpdatePost::class,
            $data,
            ['method' => 'put', 'route' => "posts/{$this->post->id}"]
        )
            ->assertValidationFailed()
            ->assertValidationErrors(['user_id'])
            ->assertValidationErrorsMissing(['content'])
            ->assertValidationMessages(['User is not valid']);
    }

    /** @test */
    public function form_request_will_authorize_request()
    {
        $this->formRequest(
            UpdatePost::class,
            [],
            ['method' => 'put', 'route' => "posts/{$this->post->id}"]
        )->assertAuthorized();

        // test the ability to build form request using the intuitive methods
        $this->formRequest(UpdatePost::class)
            ->put()
            ->withRoute("posts/{$this->post->id}")
            ->assertAuthorized();
    }

    /** @test */
    public function form_request_will_not_authorize_request()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);
        $this->formRequest(
            UpdatePost::class,
            [],
            ['method' => 'put', 'route' => "posts/{$this->post->id}"]
        )->assertNotAuthorized();
    }
}
