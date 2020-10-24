<?php

namespace MohammedManssour\FormRequestTester\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MohammedManssour\FormRequestTester\Tests\Stubs\Database\factories\PostFactory;

class Post extends Model
{
    use HasFactory;

    public $fillable = ['content', 'user_id'];

    public static function newFactory()
    {
        return new PostFactory();
    }
}
