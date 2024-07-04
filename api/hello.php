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

if ($location_data === FALSE) {
    $city = 'Unknown';
    $error = 'Failed to retrieve location data';
} else {
    $loc_o = json_decode($location_data, true);
    if ($loc_o && $loc_o['status'] == 'success') {
        $city = $loc_o['city'];
        $error = null;
    } else {
        $city = 'Unknown';
        $error = isset($loc_o['message']) ? $loc_o['message'] : 'Unknown error';
    }
}

$response = [
    "client_ip" => $client_ip,
    "location" => $city,
    "greeting" => "Hello, {$visitor_name}!",
    "error" => $error
];

echo json_encode($response);

