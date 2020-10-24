<?php

namespace MohammedManssour\FormRequestTester\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use MohammedManssour\FormRequestTester\Tests\Stubs\Database\factories\UserFactory;

class User extends Authenticatable
{
    use HasFactory;

    public $fillable = ['name', 'email', 'password'];

    public static function newFactory()
    {
        return new UserFactory();
    }
}
