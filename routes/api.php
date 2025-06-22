<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Blog\PostController;
use App\Http\Controllers\Api\Blog\CategoryController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/blog/posts', [PostController::class, 'index']);

Route::get('/posts/{id}', [PostController::class, 'show']);

Route::get('/blog/categories', [CategoryController::class, 'index']);
Route::get('/blog/categories/{id}', [CategoryController::class, 'show']);
Route::post('/blog/categories', [CategoryController::class, 'store']);
Route::put('/blog/categories/{id}', [CategoryController::class, 'update']);
Route::delete('/blog/categories/{id}', [CategoryController::class, 'destroy']);
Route::patch('/posts/{id}', [PostController::class, 'update']);
Route::delete('/posts/{id}', [PostController::class, 'destroy']);
Route::post('/blog/posts', [PostController::class, 'store']);
Route::post('/blog/posts/check-slug', [PostController::class, 'checkSlug']);
Route::post('/blog/categories/check-slug', [CategoryController::class, 'checkSlug']);
