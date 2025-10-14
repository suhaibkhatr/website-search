<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, Searchable;

    public $afterCommit = true;

    protected $fillable = ['name', 'description', 'category', 'price'];

    public function toSearchableArray(): array
    {
        return [
            'name'        => $this->name,
            'description' => strip_tags((string) $this->description),
            'category'    => (string) $this->category,
            'price'       => (float) $this->price,
        ];
    }
}
