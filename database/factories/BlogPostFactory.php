<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'        => $this->faker->sentence(),
            'body'         => '<p>'.implode('</p><p>', $this->faker->paragraphs(5)).'</p>',
            'tags'         => implode(',', $this->faker->words(3)),
            'published_at' => $this->faker->dateTimeBetween('-2 years', 'now'),
        ];
    }
}
