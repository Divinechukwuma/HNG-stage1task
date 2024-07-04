<?php

header('Content-Type: application/json');

// Function to get the client IP address
function get_client_ip() {
    foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $IPaddress) {
                $IPaddress = trim($IPaddress); // Just to be safe

                if (filter_var($IPaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $IPaddress;
                }
            }
        }
    }
    return 'UNKNOWN';
}

$visitor_name = isset($_GET['visitor_name']) ? $_GET['visitor_name'] : 'Guest';

// Get client IP
$client_ip = get_client_ip();

// Using a free API service to get location
$location_api_url = "http://ip-api.com/json/{$client_ip}";
$location_data = file_get_contents($location_api_url);

$city = 'Unknown';
$error = null;
$temperature = 'Unknown';

if ($location_data !== FALSE) {
    $loc_o = json_decode($location_data, true);
    if ($loc_o && $loc_o['status'] == 'success') {
        $city = $loc_o['city'];
        
        // Get temperature data using OpenWeatherMap API
        $api_key = '2f4692d4ab9f509fdde0fac002984034'; // Replace with your OpenWeatherMap API key
        $weather_api_url = "http://api.openweathermap.org/data/2.5/weather?q={$city}&units=metric&appid={$api_key}";
        $weather_data = file_get_contents($weather_api_url);

        if ($weather_data !== FALSE) {
            $weather_o = json_decode($weather_data, true);
            if ($weather_o && $weather_o['cod'] == 200) {
                $temperature = $weather_o['main']['temp'];
            } else {
                $error = isset($weather_o['message']) ? $weather_o['message'] : 'Failed to retrieve temperature data';
            }
        } else {
            $error = 'Failed to retrieve weather data';
        }
    } else {
        $error = isset($loc_o['message']) ? $loc_o['message'] : 'Failed to retrieve location data';
    }
} else {
    $error = 'Failed to retrieve location data';
}

$response = [
    "client_ip" => $client_ip,
    "location" => $city,
    "greeting" => "Hello, {$visitor_name}!, the temperature is {$temperature} degrees Celsius in {$city}",
    "error" => $error
];

echo json_encode($response);
?>
