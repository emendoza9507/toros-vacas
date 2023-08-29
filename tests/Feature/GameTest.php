<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_game_with_out_api_key(): void
    {
        $data = ['username' => 'usertest', 'age' => 23];

        $response = $this->post('/api/game/create', $data);

        $response
            ->assertStatus(401);
    }

    public function test_create_game(): void
    {
        $data = ['username' => fake()->userName(), 'age' => fake()->numberBetween(12, 70)];
        $response = $this->post('/api/game/create', $data, [
            'X-API-KEY' => env('API_KEY')
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonFragment([
                'status' => 'success',
                'code' => 200
            ]);
    }

    public function test_propose_combination_and_not_win(): void
    {
        $fake_game = Game::createNewGame(
            fake()->userName(),
            fake()->numberBetween(12, 70)
        );

        $fake_game->save();

        $auth_key = $fake_game->auth_key;
        $game_id = $fake_game->id;
        $combination = Game::generateSecret();

        $response = $this->post('/api/game/'.$game_id.'/propose', [
            'auth_key' => $auth_key,
            'combination' => $combination
        ], ['X-API-KEY' => env('API_KEY')]);

        if($response->assertStatus(200)) {
            $response->assertJsonFragment([
                'combination' => $combination,                
            ]);
        }
    }

    public function test_propose_combination_and_win(): void
    {
        $fake_game = Game::createNewGame(
            fake()->userName(),
            fake()->numberBetween(12, 70)
        );

        $fake_game->save();

        $auth_key = $fake_game->auth_key;
        $game_id = $fake_game->id;
        $combination = $fake_game->secret;

        $this->post('/api/game/'.$game_id.'/propose', [
            'auth_key' => $auth_key,
            'combination' => $combination
        ], ['X-API-KEY' => env('API_KEY')]);

        $response = $this->post('/api/game/'.$game_id.'/propose', [
            'auth_key' => $auth_key,
            'combination' => $combination
        ], ['X-API-KEY' => env('API_KEY')]);


        $response
            ->assertStatus(202)
            ->assertJson([
                'message' => 'Game Win!',                
            ]);
    }

    public function test_propose_combination_and_repeat(): void
    {
        $fake_game = Game::createNewGame(
            fake()->userName(),
            fake()->numberBetween(12, 70)
        );
        
        $fake_game->save();
        $fake_game->gameOver();

        $auth_key = $fake_game->auth_key;
        $game_id = $fake_game->id;
        $combination = Game::generateSecret();

        $this->post('/api/game/'.$game_id.'/propose', [
            'auth_key' => $auth_key,
            'combination' => $combination
        ], ['X-API-KEY' => env('API_KEY')]);

        $response = $this->post('/api/game/'.$game_id.'/propose', [
            'auth_key' => $auth_key,
            'combination' => $combination
        ], ['X-API-KEY' => env('API_KEY')]);

        $response
            ->assertStatus(201)
            ->assertJson([
                'message' => 'Los digitos ya fueron enviados en el mismo orden',                
            ]);
    }
}
