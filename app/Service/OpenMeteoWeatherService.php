<?php

namespace App\Service;

use App\Interface\Weather;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenMeteoWeatherService implements Weather
{
    public function __construct(
        protected string $weatherUrl = '',
        protected string $geoUrl = ''
    ) {
    }

    public function get(string $name = '', string $country = '')
    {
        if (! $name || ! $country) {
            return null;
        }

        $locationDetail = $this->getGeoLocation($name, $country);

        if (! $locationDetail) {
            return null;
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
            Log::error($th->getMessage());
        }
    }

    protected function getGeoLocation($name, $country)
    {
        try {
            $response = Http::retry(3)
                ->withQueryParameters([
                    'name' => $name,
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
            Log::error($th->getMessage());
        }
    }
}
