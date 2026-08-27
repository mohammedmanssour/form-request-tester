<?php

namespace MohammedManssour\FormRequestTester\Tests\Stubs\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use MohammedManssour\FormRequestTester\Tests\Stubs\Models\Post;
use MohammedManssour\FormRequestTester\Tests\Stubs\Models\User;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Post>
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => $this->faker->paragraph(),
            'user_id' => User::factory()
        ];
    }
}
