<?php

namespace Tests\Feature;

use App\Http\Controllers\API\TournamentController as APITournamentController;
use App\Http\Controllers\TournamentController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Tournament;
use Illuminate\Http\Request;

use Illuminate\Http\Response;
use Mockery;

class ApiTournamentControllerTest extends TestCase {

    public function testStore() {

        $requestData = [
            'title' => 'Tournament 1',
            'type' => 'Soccer',
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-05',
            'location' => 'New York',
            'prize' => 1000,
        ];

        $mockRequest = Mockery::mock(Request::class);

        $mockRequest->shouldReceive('validate')->once()->with([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'location' => 'required|string|max:255',
            'prize' => 'required|integer|min:1',
        ])->andReturn($requestData);

        $mockTournament = Mockery::mock(Tournament::class);
        $mockTournament->shouldReceive('create')->once()->with($requestData)->andReturnSelf();

        Tournament::shouldReceive('create')->once()->with($requestData)->andReturn($mockTournament);


        $controller = new APITournamentController();
        $response = $controller->store($mockRequest);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['message' => "Created Successuflly"]),
            $response->getContent()
        );

        Tournament::shouldHaveReceived('create')->once()->with($requestData);

    }

}