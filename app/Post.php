<?php

namespace App;

use App\Collections\PostCollection;
use App\Traits\ManipulatesText;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use ManipulatesText, SoftDeletes, HasFactory;

    protected $table = 'posts';

    protected $fillable = [
        'title',
        'description',
        'content',
        'slug',
        'published_at',
        'archived_at',
        'image'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function previous()
    {
        if ($this->published_at === null) {
            return null;
        }

        return self::query()->where('published_at', '<', $this->published_at)->orderByDesc('published_at')->orderByDesc('id')->first();
    }

    public function next()
    {
        if ($this->published_at === null) {
            return null;
        }

        return self::query()->where('published_at', '>', $this->published_at)->orderBy('published_at')->orderBy('id')->first();
    }

    public function getContentAttribute($content)
    {
        return app(Markdown::class)->toHtml($content);
    }

    public function getImageAttribute($value)
    {
        if ($value) {
            return $value;
        }

        return url(config('page.default_image'));
    }

    public function newCollection(array $models = [])
    {
        return new PostCollection($models);
    }

    protected static function booted()
    {
        static::creating(function ($post) {
            if ( ! isset($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });

        static::updating(function ($post) {
            if ($post->isPublished() && $post->isDirty("title")) {
                $redirect = Redirect::firstOrCreate(['slug' => $post->slug], ['post_id' => $post->id]);
                $redirect->save();
            }

            if ($post->isDirty("title")) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPublished()
    {
        return $this->published_at !== null && $this->published_at <= now();
    }
}
