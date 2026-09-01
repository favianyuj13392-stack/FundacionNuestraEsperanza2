<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ATC\AtcSubscription;
use App\Services\ATC\Atc3dsService;
use Illuminate\Support\Facades\Log;

class ProcessRecurringDonationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atc:process-recurring-donations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa automáticamente los cobros mensuales recurrentes MIT de donaciones con tarjetas tokenizadas (ATC / Cybersource)';

    /**
     * Execute the console command.
     */
    public function handle(Atc3dsService $atcService): int
    {
        $this->info('Iniciando procesamiento de donaciones recurrentes por tarjeta (ATC)...');

        $subscriptions = AtcSubscription::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('next_billing_at')
                      ->orWhere('next_billing_at', '<=', now());
            })
            ->with(['paymentProfile', 'user'])
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No hay suscripciones pendientes de cobro el día de hoy.');
            return Command::SUCCESS;
        }

        $this->info("Se encontraron {$subscriptions->count()} suscripciones activas pendientes de cobro.");
        $successCount = 0;
        $failedCount = 0;

        foreach ($subscriptions as $subscription) {
            $this->line("Procesando suscripción #{$subscription->id} por {$subscription->currency} {$subscription->amount}...");

            try {
                $result = $atcService->processRecurringCharge($subscription);

                if ($result['success']) {
                    $successCount++;
                    $this->info(" -> Cobro exitoso! Transacción #{$result['transactionId']}");
                } else {
                    $failedCount++;
                    $this->error(" -> Cobro rechazado: " . ($result['message'] ?? 'Error desconocido'));
                }
            } catch (\Throwable $e) {
                $failedCount++;
                Log::error("Excepción en cobro recurrente #{$subscription->id}: " . $e->getMessage());
                $this->error(" -> Excepción: {$e->getMessage()}");
            }
        }

        $this->info("Procesamiento finalizado. Exitosos: {$successCount}, Fallidos: {$failedCount}.");

        return Command::SUCCESS;
    }
}
