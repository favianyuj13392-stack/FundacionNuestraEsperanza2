<?php
$url = 'https://api.fundacion-nuestra-esperanza.cloud/api/public/request-qr';
$data = array(
    'is_anonymous' => false,
    'tier_id' => 6,
    'donor_name' => 'Super',
    'donor_ci' => '1099536',
    'donor_phone' => '77537423'
);
$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\nAccept: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    )
);
$context  = stream_context_create($options);
$result = @file_get_contents($url, false, $context);
if ($result === FALSE) {
    echo "Error HTTP\n";
    var_dump($http_response_header);
} else {
    echo "Respuesta:\n";
    echo $result;
}
