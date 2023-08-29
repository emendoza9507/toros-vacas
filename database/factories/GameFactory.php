<?php

namespace Database\Factories;

use App\Models\Game;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $secret = Game::generateSecret();

        $currentDate = date('d-m-Y H:i:s');
        $expiresDate = strtotime("+".env('APP_TIME_EXPIRES')." minute", strtotime($currentDate));

        $win = fake()->randomElement([true, false]);

        $name = fake()->userName();
        $age = fake()->numberBetween(12, 78);
        $authKey = Game::hashKey($name, $age);

        return [
            'username' => fake()->userName(),
            'age' => fake()->numberBetween(12, 78),
            'attempts' => fake()->numberBetween(1, 1000),
            'secret' => $secret,
            'combinations' => '[]',
            'expires' => $expiresDate,
            'evaluation' => fake()->numberBetween(0, 100),
            'win' => $win,
            'game_over' => !$win ? fake()->randomElement([true, false]) : false,
            'auth_key' => $authKey
        ];
    }
}
