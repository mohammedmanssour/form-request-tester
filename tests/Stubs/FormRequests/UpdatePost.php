<?php
namespace MohammedManssour\FormRequestTester\Tests\Stubs\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use MohammedManssour\FormRequestTester\Tests\Stubs\Models\Post;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UpdatePost extends FormRequest
{
    /**
     * post to be updated
     *
     * @var \MohammedManssour\FormRequestTester\Tests\Stubs\Models\Post
     */
    public $model;

    public function rules()
    {
        return [
            'content' => ['required'],
            'user_id' => ['required', 'exists:users,id']
        ];
    }

    public function messages()
    {
        return [
            'content.required' => 'Content Field is required',
            'user_id.required' => 'User Field is required',
            'user_id.exists' => 'User is not valid',
        ];
    }

    public function authorize()
    {
        return $this->getModel()->user_id == auth()->user()->id &&
          $this->getModel()->user_id == $this->user()->id;
    }

    public function getModel()
    {
        if (!$this->model) {
            $this->model = Post::find($this->route('post'));
            throw_if(!$this->model, NotFoundHttpException::class);
        }

        return $this->model;
    }
}
