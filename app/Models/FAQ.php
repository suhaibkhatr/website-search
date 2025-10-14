<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class FAQ extends Model
{
    /** @use HasFactory<\Database\Factories\FAQFactory> */
    use HasFactory, Searchable;

    public $afterCommit = true;

    protected $fillable = ['question', 'answer'];

    public function toSearchableArray(): array
    {
        return [
            'question' => $this->question,
            'answer' => strip_tags((string) $this->answer),
        ];
    }
}
