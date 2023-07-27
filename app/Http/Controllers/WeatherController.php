<?php

namespace App\Http\Controllers;

use App\Http\Resources\WeatherResource;
use App\Interface\Weather;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    /**
     * Get weather information
     */
    public function __invoke(Request $request, Weather $weather): WeatherResource
    {
        $validated = $request->validate([
            'location' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:255'],
        ]);

        $results = $weather->get(
            name: $validated['location'],
            country: $validated['country']
        );

        if (! $results) {
            abort(404, 'The Country could not be determined');
        }

        return WeatherResource::make($results);
    }
}
