<?php

namespace App\Service;

use App\Interface\Weather;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class OpenMeteoWeatherService implements Weather
{
    public function __construct(
        protected string $weatherUrl = '',
        protected string $geoUrl = ''
    ) {
    }

    public function get(string $name = '', string $country = '')
    {
        if (!$name || !$country) {
            abort(422, 'location is required');
        }

        $locationDetail = $this->getGeoLocation($name, $country);

        if (!$locationDetail) {
            abort(422, 'location is required');
        }

        try {
            $response = Http::retry(3)
                ->withQueryParameters([
                    'longitude' => $locationDetail['longitude'],
                    'latitude' => $locationDetail['latitude'],
                    'current_weather' => true,
                ])
                ->get($this->weatherUrl);

            if ($response->failed()) {
                return null;
            }

            if ($response->successful()) {
                $result = (object) json_decode($response->body(), true)['current_weather'];

                return $result;
            }
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    protected function getGeoLocation($name, $country)
    {
        try {
            $response = Http::retry(3)
                ->withQueryParameters([
                    'name' => $name
                ])
                ->get($this->geoUrl);


            if ($response->failed()) {
                return null;
            }

            if ($response->successful()) {
                $data = collect(
                    json_decode($response->body(), true)['results']
                );

                $location = $data
                    ->filter(fn ($l) => $l['admin1'] == ucfirst($country))
                    ->first();

                return $location;
            }
        } catch (\Throwable $th) {
            dd($th);
        }
    }
}
