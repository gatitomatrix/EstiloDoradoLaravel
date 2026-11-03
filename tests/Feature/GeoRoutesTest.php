<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\GeoController;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class GeoRoutesTest extends TestCase
{
    public function test_search_routes_to_the_geo_controller_without_a_session(): void
    {
        $payload = [['lat' => '-10.66848', 'lon' => '-76.25688', 'display_name' => 'Pasco, Perú']];

        $this->partialMock(GeoController::class, function ($mock) use ($payload) {
            $mock->shouldReceive('search')->once()
                ->with(Mockery::on(fn (Request $request) => $request->query('q') === 'Pasco'))
                ->andReturn(response()->json($payload));
        });

        $this->getJson('/api/geo/search?q=Pasco')
            ->assertOk()
            ->assertExactJson($payload);
    }

    public function test_reverse_routes_to_the_geo_controller_with_the_selected_point(): void
    {
        $payload = [
            'via' => 'Jirón Lima', 'numero' => '123',
            'departamento' => 'Pasco', 'provincia' => 'Pasco',
            'distrito' => 'Chaupimarca', 'display' => 'Jirón Lima 123, Pasco',
        ];

        $this->partialMock(GeoController::class, function ($mock) use ($payload) {
            $mock->shouldReceive('reverse')->once()
                ->with(Mockery::on(fn (Request $request) =>
                    $request->query('lat') === '-10.66848' &&
                    $request->query('lon') === '-76.25688'))
                ->andReturn(response()->json($payload));
        });

        $this->getJson('/api/geo/reverse?lat=-10.66848&lon=-76.25688')
            ->assertOk()
            ->assertExactJson($payload);
    }

    public function test_empty_search_does_not_contact_a_geocoding_provider(): void
    {
        $this->getJson('/api/geo/search?q=')->assertOk()->assertExactJson([]);
    }
}

