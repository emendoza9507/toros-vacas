<?php

namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $guarded = [
    ];

    protected $hidden = [
        // 'secret',
    ];

    static public function generateSecret() {
        $arr = array_rand([
            1, 2, 3, 4, 5, 6, 7, 8, 9, 0
        ], 4);
        return join($arr);
    }

    static public function hashKey(string $name, $age) {
        return hash('SHA256', "$age.$name.".time());
    }

    static public function generateRanking(Game $game, $cached = false) {
        $games = Game::all()->sortBy(['ranking' => 'ASC']);        

        foreach($games as $g) {
            $g->getEvaluation();
        }

        $games_arr = array(...$games);
        
        if(!$cached)
        usort($games_arr , function (Game $g1, Game $g2) {
            if($g1->isWin() && $g2->isWin()) {
                if($g1->evaluation > $g2->evaluation) {
                    return 1;
                } else {
                    return -1;
                }
            } 

            if($g1->isWin() && !$g2->isWin()) {
                return -1;
            }

            if(!$g1->isWin() && $g2->isWin()) {
                return 1;
            }

            if($g1->evaluation > $g2->evaluation) {
                return 1;
            }

            return -1;
        });

        $ranking = 0;
        foreach ($games_arr as $key => $value) {
            if($value->id == $game->id) {
                $ranking = $key;
                break;
            }
        }

        $game->ranking = $ranking + 1;
        $game->save();

        return [$ranking + 1, $games_arr];
    }

    static public function createNewGame(string $username, $age) {
        $expiresTime = env('APP_TIME_EXPIRES');
        $expiresDate =  time() + $expiresTime * 60;

        //inicializamos un nuevo juego con los datos necesarios
        return new Game([
            'username' => $username,
            'age' => $age,
            'secret' => Game::generateSecret(),
            'attempts' => 0,
            'evaluation' => 0,
            'combinations' => json_encode([]),
            'win' => false,
            'game_over' => false,
            'expires' => $expiresDate,
            'auth_key' => Game::hashKey($username, $age)
        ]);
    }

    public function checkIsExpires() {
        return time() > $this->expires;
    }

    public function hasCombination($combination) {
        $combinations = json_decode($this->combinations);
        
        foreach($combinations as $c) {
            if($c->combination === $combination) {
                return $c;
            }
        }
        
        return false;
    }

    public function checkCombination($combination) {
        $combination = strval($combination);
        $secret = $this->secret;

        $toros = 0;
        $vacas = 0;
        
        foreach(str_split($combination) as $key => $l) {
            $l_secret_pos = strpos($secret, $l);
            if($l_secret_pos === $key) {
                $toros++;
            } else if($l_secret_pos > -1) {
                $vacas++;
            }
        }

        $result = ['combination' => $combination, 'T' => $toros, 'V' => $vacas, 'result' => "$toros"."T"."$vacas"."V"];

        $this->attempts = $this->attempts + 1;
        
        if($toros > 0 || $vacas > 0) {
            $combinations = json_decode($this->combinations);
            array_push($combinations, $result);
            $this->combinations = json_encode($combinations);
        }

        if($toros == 4) {
            $this->gameWin(flash: false);
        }

        $this->save();

        return $result;
    }

    public function isWin() {
        return $this->win;
    }

    public function isGameOver() {
        return $this->geme_over;
    }

    public function gameOver() {
        $this->game_over = true;
        $this->save();
    }

    public function gameWin($flash = true) {
        $this->win = true;

        if($flash) {
            $this->save();
        }
    }

    public function getTimeOfGame() {
        $createAt = $this->created_at;
        $currentDate = date('Y-m-d H:i:s');
        return strtotime($currentDate) - strtotime($createAt);
    }

    public function isGameEnd() {
        return $this->isWin() || $this->isGameOver();
    }

    public function getEvaluation() {

        if(!$this->isGameEnd()) {
            $this->evaluation = $this->getTimeOfGame() / 2 + $this->attempts;
            $this->save();
        }

        return $this->evaluation;
    }
}
