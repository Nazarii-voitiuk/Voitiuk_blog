<?php

namespace App\Http\Controllers\Api\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Blog\Post\StoreRequest;
use App\Http\Requests\Api\Blog\Post\UpdateRequest;
use Illuminate\Http\Request;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $posts = BlogPost::with(['user', 'category'])->paginate($perPage);
        return response()->json($posts);
    }

    public function show($id)
    {
        $post = BlogPost::with(['user:id,name', 'category:id,title'])->find($id);

        if (!$post) {
            return response()->json(['error' => 'Пост не знайдено'], 404);
        }

        return response()->json($post);
    }

    public function checkSlug(Request $request)
    {
        $slug = $request->input('slug');
        $exists = BlogPost::where('slug', $slug)->exists();

        return response()->json(['exists' => $exists]);
    }

    public function update(UpdateRequest $request, $id)
    {
        $post = BlogPost::find($id);

        if (!$post) {
            return response()->json(['error' => 'Пост не знайдено'], 404);
        }

        $post->fill($request->validated());
        $post->save();

        return response()->json($post);
    }

    public function destroy($id)
    {
        $post = BlogPost::find($id);

        if (!$post) {
            return response()->json(['error' => 'Пост не знайдено'], 404);
        }

        $post->delete();

        return response()->json(['message' => 'Пост видалено']);
    }

    public function store(StoreRequest $request)
    {
        Log::info('Отримані дані для створення поста:', $request->all());

        $validated = $request->validated();

        // Додаємо user_id (тимчасово хардкодимо, далі можна з auth)
        $validated['user_id'] = 1;

        Log::info('Створюємо пост з даними:', $validated);

        $post = BlogPost::create($validated);

        Log::info('Пост успішно створено:', ['id' => $post->id]);

        return response()->json($post, 201);
    }
}

