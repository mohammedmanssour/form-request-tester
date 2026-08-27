<?php

namespace MohammedManssour\FormRequestTester\Tests;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
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

    private User $user;

    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->post = Post::factory()->create([
            'user_id' => $this->user
        ]);

        $this->actingAs($this->user);
    }

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
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

    #[Test]
    public function form_request_will_not_authorize_request()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->formRequest(
            UpdatePost::class,
            [],
            ['method' => 'put', 'route' => "posts/{$this->post->id}"]
        )->assertNotAuthorized();
    }

    #[Test]
    public function form_request_will_assert_the_validated_data()
    {
        $this->formRequest(UpdatePost::class)
            ->put([
                'content' => 'Fake Content',
                'user_id' => $this->user->id,
            ])
            ->withRoute("posts/{$this->post->id}")
            ->assertValidationPassed()
            ->assertValidationData(['content', 'user_id'])
            ->assertValidationDataMissing(['not_available_key']);
    }

    #[Test]
    public function substitube_binding_can_retreive_the_right_model()
    {
        Route::model('post', Post::class);

        $tester = $this->formRequest(UpdatePost::class)
            ->put([])
            ->withRoute("posts/{$this->post->id}");
        $tester->checkFormRequest();

        $this->assertInstanceOf(Post::class, $tester->formRequest()->route('post'));
    }

    #[Test]
    public function can_resolve_route_parameters_with_the_help_of_addRouteParameter_method()
    {
        $formRequestTester = $this->formRequest(UpdatePost::class)
            ->put([
                'content' => 'Fake Content',
                'user_id' => $this->user->id
            ])->addRouteParameter('post', $this->post->id)
            ->assertAuthorized()
            ->assertValidationPassed();

        $this->assertEquals(1, $formRequestTester->formRequest()->route('post'));
    }
}
