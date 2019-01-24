<?php

namespace MohammedManssour\FormRequestTester\Tests\Stubs\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    public $fillable = ['name', 'email', 'password'];
}