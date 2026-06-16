<?php
// Script actualizado para apuntar al servidor de PRODUCCION del BNB
// La mayoria de las veces, si test es test.bnb.com.bo, produccion es www.bnb.com.bo o api.bnb.com.bo
$url = 'https://test.bnb.com.bo/ClientAuthentication.API/api/v1/auth/UpdateCredentials';

$data = array(
    'AccountId' => 'FUNDESPDOM',
    'actualAuthorizationId' => '1234abcd',
    'newAuthorizationId' => 'FneDomici2026Secure!'
);

$options = array(
    'http' => array(
        'header'  => "Content-type: application/json\r\nUser-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true // Para capturar el body incluso si hay error 400/500
    ),
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
    )
);

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "URL: " . $url . "\n";
echo "Resultado de Producción: " . $result . "\n";
?>
