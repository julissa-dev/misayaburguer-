<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Checkout;

class CancelarCheckoutsExpirados extends Command
{
    protected $signature = 'checkouts:cancelar-expirados';
    protected $description = 'Cancela automáticamente los checkouts pendientes que han expirado.';

    public function handle()
    {
        $expirados = Checkout::where('estado', 'pendiente')
            ->whereNotNull('expira_en')
            ->where('expira_en', '<', now())
            ->update(['estado' => 'cancelado']);

        $this->info("Se cancelaron $expirados checkouts expirados.");
    }
}
