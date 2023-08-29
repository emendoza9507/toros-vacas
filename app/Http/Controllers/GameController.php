<?php

namespace App\Http\Controllers;

use App\Helpers\ComprobationResult;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Info(title="API Games", version="v1")
 * @OA\SecurityScheme(
 *      securityScheme="Authentication_Token",
 *      in="header",
 *      name="X-API-KEY",
 *      type="apiKey")
 */
class GameController extends Controller
{
    // /**
    //  * @OA\Get(
    //  *   tags={"Game"},
    //  *   security={{"Authentication_Token":{}}},
    //  *   path="/api/game/list",
    //  *   summary="Mostrar todos los Juegos, Solo para testear la aplicacion!!",     
    //  *   @OA\Response(response=200, description="OK"),
    //  *   @OA\Response(response=401, description="Unauthorized"),
    //  *   @OA\Response(response=404, description="Juego no encontrado"),
    //  *   @OA\Response(response=502, description="Error validando la peticion"),
    //  * )
    //  */
    // public function list()
    // {
    //     $games = Game::all()->sortBy(['evaluation' => 'DESC']);
    //     return $games;
    // }

    /**
     * @OA\Get(
     *   tags={"Game"},
     *   security={{"Authentication_Token":{}}},
     *   path="/api/game/{id}/detail",
     *   summary="Mostrar evaluacion de las combinaciones ejecutadas para el Juego.",     
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Juego no encontrado"),
     * )
     */
    public function detail($id) 
    {
        $game = Game::find($id);

        if(!$game) {
            return response()->json([
                'message' => 'El Juego solicitado no existe',                
            ], 404);
        }

        return $game->combinations;
    }

    /**
     * @OA\Get(
     *   tags={"Game"},
     *   security={{"Authentication_Token":{}}},
     *   path="/api/game/{id}/prev/{combination}",
     *   summary="Mostrar la evaluacion de la combinacion ejecutada para el Juego.",     
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     *   @OA\Response(response=404, description="Juego no encontrado"),
     * )
     */
    public function prev(Request $request, $id, $combination) 
    {
        $game = Game::find($id);

        if(!$game) {
            return response()->json([
                'message' => 'El Juego no existe.'
            ], 404);
        }
                
        $_combination = $game->hasCombination($combination);

        if(!$_combination) {
            return response()->json([
                'message' => 'No ha enviado esta combinacion.'
            ], 403);
        }

        $data = new ComprobationResult(
            $combination,
            $_combination->result,
            $_combination,
            $game->attempts,
            $game->getEvaluation(),
            $game->raiting
        );

        return response()->json($data, 200);
    }

    /**
     * @OA\Post(
     *   tags={"Game"},
     *   path="/api/game/create",
     *   security={{"Authentication_Token":{}}},
     *   summary="Crea un nuevo juego con los datos solicitados",
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"username", "age"},
     *       @OA\Property(property="username", type="string"),
     *       @OA\Property(property="age", type="number")       
     *     ),
     *   ),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function create(Request $request) 
    {
        // Validamos los datos de entrada
        $validator = Validator::make($request->all(), [
            'username' => 'required|max:50',
            'age' => 'required'
        ]);

        // Si falla mandamos creamos la respuesta con el error
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'code' => 502
            ], 502);
        }

        //Cariable de configuracion para el tiempo de expiracion de los juegos
        $expiresTime = env('APP_TIME_EXPIRES');
        $expiresDate =  time() + $expiresTime * 60;

        $username = $request->get('username');
        $age = $request->get('age');

        //inicializamos un nuevo juego con los datos necesarios
        $game = Game::createNewGame($username, $age);

        $game->save();

        //retornamos la respuesta con los datos
        return response()->json([
            'status' => 'success',
            'data' => [
                'game_id' => $game->id,
                'auth_key' => $game->auth_key,
                'expires_time' => $expiresTime,
                'expires' => $expiresDate,
                'created_at' => $game->created_at
            ],
            'code' => 200
        ], 200);
    }

    /**
     * @OA\Post(
     *   tags={"Game"},
     *   security={{"Authentication_Token":{}}},
     *   path="/api/game/{id}/propose",     * 
     *   summary="Procesa la combinacion enviada para el juego solicitado",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer"),
     *   ),
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       type="object",
     *       required={"combination", "auth_hey"},
     *       @OA\Property(property="combination", type="string"),
     *       @OA\Property(property="auth_key", type="string")       
     *     )
     *   ),
     *   @OA\Response(response=200, description="Detalle de combinacion"),
     *   @OA\Response(response=201, description="Combinacion duplicada"),
     *   @OA\Response(response=202, description="Juego Ganado"),
     *   @OA\Response(response=203, description="Juego Perdido"),
     *   @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function propose(Request $request, $id) 
    {
        // Buscar y comprobar existencia del juego solicitado
        $game = Game::find($id);

        if(!$game) {
            return response()->json([
                'data' => 'El juego no existe.'
            ], 404);
        }

        //Validar la solicitud del usuario
        $validator = Validator::make($request->all(), [
            'combination' => [
                'required',
                'regex:/^[0-9]{4}$/',
                'regex:/^(?!.*(\d).*\1)/'
            ],
            'auth_key'  =>  'required'
        ], [
            'combination.required' => 'La Combinacion es requerida',
            'combination.regex' => 'La Combinacion no cumple con el formato requerido.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
                'code' => 502
            ], 502);
        }

        $authKey = $request->get('auth_key');
        $combination = $request->get('combination');

        // Comprobar si el usuario conoce las credenciales del juego.
        if($game->auth_key != $authKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'No eres el creador del juego!',
                'code' => 401
            ], 401);
        }

        //Comprobar que el juego no este perdido
        if($game->isGameOver()) {
            return response()->json([
                'message' => 'Game Over!'
            ], 203);
        }
    
        //Conprobar que el juego no este ganado
        if($game->isWin()) {
            return response()->json([
                'message' => 'Game Win!'
            ], 202);
        }

        //Comprobar y actualizar el estado del juego.
        if($game->checkIsExpires()) {
            $game->gameOver();

            return response()->json([
                'message' => 'Game Over'
            ], 203);
        }

        if($game->hasCombination($combination)) {
            return response()->json([
                'message' => 'Los digitos ya fueron enviados en el mismo orden'
            ], 201);
        }

        $result = $game->checkCombination($combination);
        $ranking = Game::generateRanking($game);

        $data = new ComprobationResult(
            $combination,
            $result['result'],
            $result,
            $game->attempts,
            $game->getEvaluation(),
            $game->ranking
        );

        return response()->json($data, 200);
    }

    /**
     * @OA\Delete(
     *   tags={"Game"},
     *   security={{"Authentication_Token":{}}},
     *   path="/api/game/{id}/delete",     * 
     *   summary="Elimina el Juego especificado por su id.",
     *   @OA\Parameter(
     *     name="id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="number"),
     *   ),
     *   @OA\Parameter(
     *     name="auth_key",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string"),
     *   ),
     *   @OA\Response(response=200, description="OK"),
     *   @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function delete(Request $request, $id) 
    {
        $auth_key = $request->query('auth_key');

        $game = Game::find($id);

        if(!$game) {
            return response()->json([
                'message' => 'El Juego no existe.'
            ], 404);
        }

        if($game->auth_key != $auth_key) {
            return response()->json([
                'message' => 'No esta autorizado a eliminar este Juego.'
            ], 401);
        }

        $game->delete();

        return response()->json([
            'message' => 'Juego eliminado',
            'data' => $game,
        ]);
    }
}
