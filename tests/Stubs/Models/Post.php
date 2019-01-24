<?php

namespace MohammedManssour\FormRequestTester\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public $fillable = ['content', 'user_id'];
}