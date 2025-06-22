<?php

namespace App\Http\Requests\Api\Blog\Post;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $id = $this->route('id');

        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9-]+$/|unique:blog_posts,slug,' . $id,
            'content_raw' => 'nullable|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'required|integer|exists:blog_categories,id',
            'is_published' => 'boolean'
        ];
    }
}

