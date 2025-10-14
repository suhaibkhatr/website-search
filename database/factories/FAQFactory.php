<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FAQ>
 */
class FAQFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => 'How to '.$this->faker->words(3, true).'?',
            'answer'   => '<p>'.implode('</p><p>', $this->faker->paragraphs(3)).'</p>',
        ];
    }
}
