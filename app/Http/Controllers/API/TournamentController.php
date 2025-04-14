<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tournaments",
     *     summary="Get a list of all tournaments",
     *     tags={"Tournaments"},
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
     *         description="Successfully retrieved tournaments",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="tournaments", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="World Cup 2025"),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2025-06-01"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2025-07-01"),
     *                 @OA\Property(property="location", type="string", example="USA"),
     *             )),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to retrieve tournaments")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $tournaments = Tournament::all();

        return response()->json(['torunaments' => $tournaments], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/tournaments",
     *     summary="Create a new tournament",
     *     tags={"Tournaments"},
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
     *     @OA\RequestBody(
     *         required=true,
     *         description="Tournament data",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title", "type", "start_date", "end_date", "location", "prize"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="World Cup 2025"),
     *             @OA\Property(property="type", type="string", example="Football"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-06-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2025-07-01"),
     *             @OA\Property(property="location", type="string", maxLength=255, example="USA"),
     *             @OA\Property(property="prize", type="integer", minimum=1, example=50000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Tournament created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Created Successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="The title field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to create tournament")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validation = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location' => 'required|string|max:255',
            'prize' => 'required|integer|min:1',
        ]);

        Tournament::create($validation);

        return response()->json(['message' => "Created Successuflly"], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/tournaments/{id}",
     *     summary="Get a specific tournament by ID",
     *     tags={"Tournaments"},
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
     *         description="Tournament ID",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved the tournament",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="tournament", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="World Cup 2025"),
     *                 @OA\Property(property="type", type="string", example="Football"),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2025-06-01"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2025-07-01"),
     *                 @OA\Property(property="location", type="string", example="USA"),
     *                 @OA\Property(property="prize", type="integer", example=50000)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tournament not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Tournament was not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to retrieve tournament")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        $tournament = Tournament::find($id);

        if ($tournament) {
            return response()->json(['tournament' => $tournament], 200);
        }

        return response()->json(['error' => 'Tournament was not found'], 404);

    }

    /**
     * @OA\Put(
     *     path="/api/tournaments/{id}",
     *     summary="Update a specific tournament by ID",
     *     tags={"Tournaments"},
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
     *         description="Tournament ID",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Tournament data to update",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"title", "type", "start_date", "end_date", "location", "prize"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="World Cup 2025"),
     *             @OA\Property(property="type", type="string", example="Football"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2025-06-01"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2025-07-01"),
     *             @OA\Property(property="location", type="string", maxLength=255, example="USA"),
     *             @OA\Property(property="prize", type="integer", minimum=1, example=50000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tournament updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Updated Successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tournament not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Tournament was not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request - Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="The title field is required.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to update tournament")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $validation = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location' => 'required|string|max:255',
            'prize' => 'required|integer|min:1',
        ]);

        $tournament = Tournament::find($id);

        if ($tournament) {
            $tournament->update($validation);
            return response()->json(['message' => "Updated Successuflly"], 200);
        }
        
        return response()->json(['error' => "Tournament was not found"], 404);

    }

    /**
     * @OA\Delete(
     *     path="/api/tournaments/{id}",
     *     summary="Delete a specific tournament by ID",
     *     tags={"Tournaments"},
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
     *         description="Tournament ID",
     *         @OA\Schema(type="string", example="1")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Tournament deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Deleted Successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tournament not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Tournament was not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Failed to delete tournament")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {

        $tournament = Tournament::find($id);

        if ($tournament) {
            $tournament->delete();
            return response()->json(['message' => "Deleted Successuflly"], 200);
        }
        
        return response()->json(['error' => "Tournament was not found"], 404);

    }
}
