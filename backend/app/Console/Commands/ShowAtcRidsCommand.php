<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ATC\AtcTransaction;

class ShowAtcRidsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atc:rids {--limit=10 : Número de transacciones a mostrar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muestra las transacciones recientes de ATC Cybersource con sus respectivos RIDs y referencias';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $transactions = AtcTransaction::latest('id')->take($limit)->get();

        if ($transactions->isEmpty()) {
            $this->warn('No se encontraron transacciones registradas en ATC.');
            return 0;
        }

        $headers = ['ID', 'Merchant Reference', 'Request ID (RID)', 'Monto', 'ECI', 'Estado', 'Fecha (BOT)'];
        $rows = [];

        foreach ($transactions as $tx) {
            $rows[] = [
                $tx->id,
                $tx->merchant_reference_number,
                $tx->cybersource_request_id ?: 'N/A',
                "{$tx->amount} {$tx->currency}",
                $tx->eci_raw ?: 'N/A',
                $tx->status,
                $tx->created_at ? $tx->created_at->timezone('America/La_Paz')->format('Y-m-d H:i:s') : 'N/A',
            ];
        }

        $this->info("\n=== 💳 TRANSACCIONES RECIENTES ATC CYBERSOURCE ===");
        $this->table($headers, $rows);
        $this->line('');

        return 0;
    }
}
