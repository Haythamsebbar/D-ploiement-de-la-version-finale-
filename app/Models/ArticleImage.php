<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ArticleImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'path',
        'title',
        'description',
        'order',
        'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'order' => 'integer'
    ];

    protected $appends = ['url'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function getUrlAttribute()
    {
        if (empty($this->path)) {
            return asset('images/default-article-image.png');
        }
        return Storage::url($this->path);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            if ($image->path) {
                Storage::disk('public')->delete($image->path);
            }
        });
    }
} 