<?php

namespace App\Interface;

interface Weather
{
    public function get(string $name = '', string $country = '');
}
