<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Player;
use Illuminate\Http\Request;

class PlayerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tournaments/{id}/players",
     *     summary="Get players of a specific tournament by ID",
     *     tags={"Players"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer (JWT) token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Tournament ID to fetch players",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of players in the tournament",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="players",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="position", type="string", example="Forward"),
     *                     @OA\Property(property="team", type="string", example="Team A")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tournament not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Tournament not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to retrieve players")
     *         )
     *     )
     * )
     */
    public function index($id)
    {
        $players = Player::where('tournament_id', '=', $id)->get();

        return response()->json(['players' => $players], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/tournaments/{id}/players",
     *     summary="Create a new player for a specific tournament",
     *     tags={"Players"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer (JWT) token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Tournament ID to associate the player with",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Player details to be created",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"first_name", "last_name", "age", "number", "nationality"},
     *             @OA\Property(property="first_name", type="string", example="John"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="age", type="integer", example=25),
     *             @OA\Property(property="number", type="integer", example=10),
     *             @OA\Property(property="nationality", type="string", example="American")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Player created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Created Successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="object",
     *                 @OA\Property(property="first_name", type="array", @OA\Items(type="string", example="The first name field is required.")),
     *                 @OA\Property(property="last_name", type="array", @OA\Items(type="string", example="The last name field is required.")),
     *                 @OA\Property(property="age", type="array", @OA\Items(type="string", example="The age field is required.")),
     *                 @OA\Property(property="number", type="array", @OA\Items(type="string", example="The number field is required.")),
     *                 @OA\Property(property="nationality", type="array", @OA\Items(type="string", example="The nationality field is required."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to create player")
     *         )
     *     )
     * )
     */
    public function store(Request $request, $id)
    {
        $validation = $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'age' => 'required|integer',
            'number' => 'required|integer',
            'nationality' => 'required|string|max:255',
        ]);

        $player = new Player();

        $player -> first_name = $request -> first_name;
        $player -> last_name = $request -> last_name;
        $player -> age = $request -> age;
        $player -> number = $request -> number;
        $player -> nationality = $request -> nationality;
        $player -> tournament_id = $id;

        $player -> save();

        return response()->json(['message' => "Created Successuflly"], 201);
    }


    /**
     * @OA\Delete(
     *     path="/api/tournaments/{tournament_id}/players/{player_id}",
     *     summary="Delete a specific player from a tournament",
     *     tags={"Players"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer (JWT) token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="tournament_id",
     *         in="path",
     *         required=true,
     *         description="Tournament ID from which the player will be deleted",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Parameter(
     *         name="player_id",
     *         in="path",
     *         required=true,
     *         description="Player ID to be deleted",
     *         @OA\Schema(type="string", example="10")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Player successfully deleted",
     *         @OA\JsonContent(
     *             type="object"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Player or tournament not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Player or tournament not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to delete player")
     *         )
     *     )
     * )
     */
    public function destroy(string $tournament_id, string $player_id)
    {
        Player::where('tournament_id', $tournament_id)->where('id', $player_id)->delete();

        return response()->json([], 204);
    }
}
