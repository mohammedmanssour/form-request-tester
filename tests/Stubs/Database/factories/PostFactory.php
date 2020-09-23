<?php

use Faker\Generator as Faker;
use MohammedManssour\FormRequestTester\Tests\Stubs\Models\Post;
use MohammedManssour\FormRequestTester\Tests\Stubs\Models\User;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'content' => $faker->paragraph(),
        'summary' => $faker->paragraph(1),
        'user_id' => function () {
            factory(User::class)->create()->id;
        }
    ];
});
