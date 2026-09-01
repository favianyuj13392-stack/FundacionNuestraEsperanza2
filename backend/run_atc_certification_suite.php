<?php

require '/var/www/html/vendor/autoload.php';
$app = require_once '/var/www/html/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = app(App\Services\ATC\Atc3dsService::class);

$testCards = [
    [
        'brand' => 'VISA',
        'number' => '4000000000001000',
        'month' => '12',
        'year' => '2028',
        'cvv' => '123',
        'label' => 'Visa Donación Única Nacional'
    ],
    [
        'brand' => 'VISA',
        'number' => '4000000000001000',
        'month' => '12',
        'year' => '2028',
        'cvv' => '123',
        'is_recurring' => true,
        'label' => 'Visa Donación Recurrente Semilla (TMS)'
    ],
    [
        'brand' => 'MASTERCARD',
        'number' => '5100000000000008',
        'month' => '12',
        'year' => '2028',
        'cvv' => '123',
        'label' => 'Mastercard Donación Única Nacional'
    ],
    [
        'brand' => 'MASTERCARD',
        'number' => '5100000000000008',
        'month' => '12',
        'year' => '2028',
        'cvv' => '123',
        'is_recurring' => true,
        'label' => 'Mastercard Donación Recurrente Semilla (TMS)'
    ],
    [
        'brand' => 'AMEX',
        'number' => '370000000000002',
        'month' => '12',
        'year' => '2028',
        'cvv' => '1234',
        'label' => 'Amex Donación Única Nacional'
    ],
    [
        'brand' => 'AMEX',
        'number' => '370000000000002',
        'month' => '12',
        'year' => '2028',
        'cvv' => '1234',
        'is_recurring' => true,
        'label' => 'Amex Donación Recurrente Semilla (TMS)'
    ],
];

$results = [];

foreach ($testCards as $index => $tc) {
    echo "=== Ejecutando Prueba " . ($index + 1) . ": " . $tc['label'] . " ===\n";

    try {
        // Paso 1: Setup Authentication
        $setup = $service->setupAuthentication([
            'card_number' => $tc['number'],
            'expiration_month' => $tc['month'],
            'expiration_year' => $tc['year'],
        ]);

        if (!$setup['success']) {
            echo "Error en Paso 1 (Setup): " . json_encode($setup) . "\n";
            continue;
        }

        sleep(1);

        $sessionId = 'redenlace_000021cert_' . uniqid();

        // Paso 3: Check Enrollment
        $enroll = $service->checkEnrollment([
            'referenceId' => $setup['referenceId'],
            'fingerprintSessionId' => $sessionId,
            'merchantReferenceNumber' => $setup['merchantReferenceNumber'],
            'amount' => 10,
            'currency' => 'BOB',
            'card_number' => $tc['number'],
            'expiration_month' => $tc['month'],
            'expiration_year' => $tc['year'],
            'cvv' => $tc['cvv'],
            'first_name' => 'JUAN',
            'last_name' => 'PEREZ',
            'email' => 'juan.perez@fundacion.org',
            'country' => 'BO',
            'state' => 'L',
            'locality' => 'La Paz',
            'postal_code' => '0000',
            'address1' => 'Av. Principal 123'
        ]);

        if (!$enroll['success']) {
            echo "Error en Paso 3 (Check Enrollment): " . json_encode($enroll) . "\n";
            continue;
        }

        sleep(1);

        // Paso 6: Process Payment
        $pay = $service->processPayment([
            'merchantReferenceNumber' => $setup['merchantReferenceNumber'],
            'amount' => 10,
            'currency' => 'BOB',
            'card_number' => $tc['number'],
            'expiration_month' => $tc['month'],
            'expiration_year' => $tc['year'],
            'cvv' => $tc['cvv'],
            'card_type' => $tc['brand'],
            'first_name' => 'JUAN',
            'last_name' => 'PEREZ',
            'email' => 'juan.perez@fundacion.org',
            'country' => 'BO',
            'state' => 'L',
            'locality' => 'La Paz',
            'postal_code' => '0000',
            'address1' => 'Av. Principal 123',
            'fingerprintSessionId' => $sessionId,
            'eci' => $enroll['eci'],
            'cavv' => $enroll['cavv'],
            'xid' => $enroll['xid'],
            'threeDSServerTransactionId' => $enroll['threeDSServerTransactionId'] ?? null,
            'is_recurring' => !empty($tc['is_recurring'])
        ]);

        if ($pay['success']) {
            echo "--> ÉXITO: Transacción #" . $pay['transactionId'] . " | RID: " . $pay['cybersourceRequestId'] . "\n\n";
            $results[] = [
                'brand' => $tc['brand'],
                'label' => $tc['label'],
                'merchant_ref' => $setup['merchantReferenceNumber'],
                'rid' => $pay['cybersourceRequestId'],
                'transaction_id' => $pay['transactionId'],
                'eci' => $enroll['eci'] ?? '05',
                'status' => $pay['status'],
            ];
        } else {
            echo "--> ERROR en Cobro: " . json_encode($pay) . "\n\n";
        }
    } catch (\Throwable $e) {
        echo "--> EXCEPCION en Prueba " . ($index + 1) . ": " . $e->getMessage() . "\n\n";
    }

    sleep(1);
}

echo "=== RESULTADOS FINALES DE CERTIFICACIÓN ===\n";
echo json_encode($results, JSON_PRETTY_PRINT) . "\n";
