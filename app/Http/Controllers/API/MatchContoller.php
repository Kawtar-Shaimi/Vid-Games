<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchContoller extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/matches",
     *     summary="Get a list of all tournament matches with associated players",
     *     tags={"Matches"},
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
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved a list of matches",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="matches",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", example="1"),
     *                     @OA\Property(property="tournament_id", type="string", example="1"),
     *                     @OA\Property(property="date", type="string", example="2025-04-01T10:00:00"),
     *                     @OA\Property(property="location", type="string", example="Stadium A"),
     *                     @OA\Property(
     *                         property="players",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="string", example="10"),
     *                             @OA\Property(property="first_name", type="string", example="John"),
     *                             @OA\Property(property="last_name", type="string", example="Doe"),
     *                             @OA\Property(property="number", type="integer", example=10),
     *                             @OA\Property(property="nationality", type="string", example="American")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to retrieve matches")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $matches = TournamentMatch::with('players')->get();

        return response()->json(['matches' => $matches], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/matches",
     *     summary="Create a new tournament match",
     *     tags={"Matches"},
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
     *     @OA\Response(
     *         response=201,
     *         description="Match created successfully",
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
     *                 @OA\Property(property="date", type="array", @OA\Items(type="string", example="The date field is required.")),
     *                 @OA\Property(property="time", type="array", @OA\Items(type="string", example="The time field is required.")),
     *                 @OA\Property(property="tournament_id", type="array", @OA\Items(type="string", example="The tournament id field is required.")),
     *                 @OA\Property(property="city", type="array", @OA\Items(type="string", example="The city field is required.")),
     *                 @OA\Property(property="host_team_name", type="array", @OA\Items(type="string", example="The host team name field is required.")),
     *                 @OA\Property(property="guest_team_name", type="array", @OA\Items(type="string", example="The guest team name field is required.")),
     *                 @OA\Property(property="players", type="array", @OA\Items(type="string", example="The players field is required.")),
     *                 @OA\Property(property="host_team_score", type="array", @OA\Items(type="string", example="The host team score field is required.")),
     *                 @OA\Property(property="guest_team_score", type="array", @OA\Items(type="string", example="The guest team score field is required."))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to create match")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'tournament_id' => 'required|integer|exists:tournaments,id',
            'city' => 'required|string',
            'host_team_name' => 'required|string',
            'guest_team_name' => 'required|string',
            'players' => 'required|array',
            'players.*' => 'integer',
            'host_team_score' => 'required|integer',
            'guest_team_score' => 'required|integer',
        ]);

        // Insert Into `matches` Table

        $match = new TournamentMatch();

        $match -> date = $request -> date;
        $match -> time = $request -> time;
        $match -> tournament_id = $request -> tournament_id;
        $match -> city = $request -> city;
        $match -> host_team_name = $request -> host_team_name;
        $match -> guest_team_name = $request -> guest_team_name;
        $match -> host_team_score = $request -> host_team_score;
        $match -> guest_team_score = $request -> guest_team_score;

        $match -> save();

        // Insert Into `matches_players` Table

        $insertData = [];
        foreach ($request->players as $player) {
            $insertData[] = [
                'match_id' => $match->id,
                'player_id' => $player
            ];
        }

        DB::table('matches_players')->insert($insertData);

        return response()->json(['message' => "Created Successuflly"], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/matches/{id}",
     *     summary="Get a specific tournament match with associated players",
     *     tags={"Matches"},
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
     *         description="ID of the match to retrieve",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved match",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="match",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="1"),
     *                 @OA\Property(property="tournament_id", type="string", example="1"),
     *                 @OA\Property(property="date", type="string", example="2025-04-01"),
     *                 @OA\Property(property="time", type="string", example="14:30"),
     *                 @OA\Property(property="city", type="string", example="New York"),
     *                 @OA\Property(property="host_team_name", type="string", example="Team A"),
     *                 @OA\Property(property="guest_team_name", type="string", example="Team B"),
     *                 @OA\Property(property="host_team_score", type="integer", example=3),
     *                 @OA\Property(property="guest_team_score", type="integer", example=2),
     *                 @OA\Property(
     *                     property="players",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="string", example="10"),
     *                         @OA\Property(property="first_name", type="string", example="John"),
     *                         @OA\Property(property="last_name", type="string", example="Doe"),
     *                         @OA\Property(property="number", type="integer", example=10),
     *                         @OA\Property(property="nationality", type="string", example="American")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Match not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Match not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to retrieve match")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $match = TournamentMatch::with('players')->find($id);

        return response()->json(['match' => $match]);
    }

    /**
     * @OA\Put(
     *     path="/api/matches/{id}",
     *     summary="Update a tournament match by ID",
     *     tags={"Matches"},
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
     *         description="ID of the match to update",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="date", type="string", format="date", example="2025-04-01"),
     *             @OA\Property(property="time", type="string", format="time", example="14:30"),
     *             @OA\Property(property="tournament_id", type="integer", example=1),
     *             @OA\Property(property="city", type="string", example="New York"),
     *             @OA\Property(property="host_team_name", type="string", example="Team A"),
     *             @OA\Property(property="guest_team_name", type="string", example="Team B"),
     *             @OA\Property(property="host_team_score", type="integer", example=3),
     *             @OA\Property(property="guest_team_score", type="integer", example=2)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully updated the match",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Updated Successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Match not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Match Not Found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Invalid input data")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Failed to update match")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'date' => 'date',
            'time' => 'date_format:H:i',
            'tournament_id' => 'integer|exists:tournaments,id',
            'city' => 'string',
            'host_team_name' => 'string',
            'guest_team_name' => 'string',
            'host_team_score' => 'integer',
            'guest_team_score' => 'integer',
        ]);

        $match = TournamentMatch::find($id);

        if (!$match) {
            return response()->json(['message' => "Match Not Found"], 404);
        }

        $match->update($data);

        return response()->json(['message' => "Updated Successuflly"], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/matches/{id}",
     *     summary="Delete a tournament match by ID",
     *     tags={"Matches"},
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
     *         description="ID of the match to delete",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successfully deleted the match",
     *         @OA\JsonContent(
     *             type="object",
     *             example={}
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Match not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Match Not Found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Failed to delete match")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        TournamentMatch::destroy($id);

        return response()->json([], 204);
    }
}
