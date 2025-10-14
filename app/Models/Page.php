<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Page extends Model
{
    /** @use HasFactory<\Database\Factories\PageFactory> */
    use HasFactory, Searchable;

    public $afterCommit = true;

    protected $fillable = ['title', 'content'];

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'content' => strip_tags((string) $this->content),
        ];
    }
}
