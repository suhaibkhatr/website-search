<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class BlogPost extends Model
{
    /** @use HasFactory<\Database\Factories\BlogPostFactory> */
    use HasFactory, Searchable;

    public $afterCommit = true;

    protected $fillable = ['title', 'body', 'tags', 'published_at'];

    public function toSearchableArray(): array
    {
        return [
            'title'        => $this->title,
            'body'         => strip_tags((string) $this->body),
            'tags'         => (string) $this->tags,
            'published_at' => optional($this->published_at)?->toDateTimeString(),
        ];
    }
}
