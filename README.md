# Setup

If your using docker you can ./vendor/bin/sail up -d. Copy the .env.example I've left all the setting ready to be use for ease of use (I normally would never allow sensitive data to be stored in github). If you don't use sail then you need to change the database credentials You need to run the migrations and seed the data.

The user login details are email: test@example.com and password: password.

I'm using open-meteo api and this api doesn't require a api key to be provided.

## Endpoints

-   POST /api/login {email, password, device_name} are required
-   POST /api/logout
-   POST /api/weather {location, country} are required

## Overview

I've created a login controller to generate api tokens using sanctum, and a logout controller deletes that token. I've created a WeatherInterface that is implemented in the OpenMeteoWeatherService and this is bind into the service container, so it can be swapped out with other weather service classes. I've added validation to the weather endpoint and I've used pint to apply coding styles.

I've added test for the authentication endpoint and testing the weather endpoint.
