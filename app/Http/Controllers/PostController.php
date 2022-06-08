<?php

namespace App\Http\Controllers;

use App\PublishedPost;

class PostController extends Controller
{
    public function index()
    {
        return view('posts.index', [
            'groupedPosts' => PublishedPost::query()->latest('published_at')->get()->groupByYear()
        ]);
    }

    public function show(PublishedPost $post)
    {
        return view('posts.show', [
            'openGraph' => true,
            'post' => $post,
            'previous' => $post->previous(),
            'next' => $post->next(),
        ]);
    }
}
