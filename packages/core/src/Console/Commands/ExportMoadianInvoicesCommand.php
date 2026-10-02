<?php

declare(strict_types=1);

namespace Reyhan\Core\Console\Commands;

use Reyhan\Core\Actions\Tax\GenerateMoadianInvoiceAction;
use Reyhan\Core\Enums\OrderStatus;
use Reyhan\Core\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;

final class ExportMoadianInvoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reyhan:tax:export-moadian
                            {--from= : Start date filter (YYYY-MM-DD)}
                            {--to= : End date filter (YYYY-MM-DD)}
                            {--format=json : Output format: json or csv}
                            {--output= : Custom destination path for exported file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and export official Iranian Taxpayer System (سامانه مؤدیان) electronic invoices';

    /**
     * Execute the console command.
     */
    public function handle(GenerateMoadianInvoiceAction $generator): int
    {
        $this->newLine();
        $this->line('<fg=cyan;options=bold>┏━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┓</>');
        $this->line('<fg=cyan;options=bold>┃       🏛️  Moadian Tax Invoices Export Engine       ┃</>');
        $this->line('<fg=cyan;options=bold>┗━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━┛</>');
        $this->newLine();

        $query = Order::query()
            ->whereNotNull('paid_at')
            ->where('status', '!=', OrderStatus::Cancelled)
            ->with(['items.productVariant.product', 'user']);

        if ($from = $this->option('from')) {
            $query->whereDate('paid_at', '>=', Carbon::parse((string) $from));
        }

        if ($to = $this->option('to')) {
            $query->whereDate('paid_at', '<=', Carbon::parse((string) $to));
        }

        $orders = $query->orderBy('paid_at')->get();

        if ($orders->isEmpty()) {
            $this->warn('⚠ No settled orders found matching the specified tax date range.');

            return self::SUCCESS;
        }

        $invoices = [];
        $totalTaxable = 0;
        $totalVat = 0;

        $progressBar = $this->output->createProgressBar($orders->count());
        $progressBar->start();

        foreach ($orders as $order) {
            $invoiceData = $generator->execute($order);
            $invoices[] = [
                'order_number' => $order->order_number,
                'tax_uid' => $invoiceData['tax_uid'],
                'pattern' => $order->is_corporate_invoice ? 'Pattern 1 (B2B)' : 'Pattern 2 (B2C)',
                'paid_at' => $order->paid_at?->toIso8601String(),
                'total_amount_rials' => $order->final_payable,
                'tax_amount_rials' => $order->tax_amount,
                'payload' => $invoiceData['payload'],
            ];

            $totalTaxable += (int) $order->items_subtotal;
            $totalVat += (int) $order->tax_amount;

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $format = strtolower((string) ($this->option('format') ?? 'json'));
        $defaultPath = storage_path('app/tax/moadian_export_'.now()->format('Y_m_d_His').".{$format}");
        $outputPath = (string) ($this->option('output') ?: $defaultPath);

        File::ensureDirectoryExists(dirname($outputPath));

        if ($format === 'csv') {
            $fileHandle = fopen($outputPath, 'w');
            if ($fileHandle !== false) {
                fputcsv($fileHandle, ['Order Number', 'Tax UID', 'Pattern', 'Paid At', 'Total Rials', 'VAT Rials']);
                foreach ($invoices as $inv) {
                    fputcsv($fileHandle, [
                        $inv['order_number'],
                        $inv['tax_uid'],
                        $inv['pattern'],
                        $inv['paid_at'],
                        $inv['total_amount_rials'],
                        $inv['tax_amount_rials'],
                    ]);
                }
                fclose($fileHandle);
            }
        } else {
            File::put($outputPath, (string) json_encode([
                'exported_at' => now()->toIso8601String(),
                'invoice_count' => count($invoices),
                'total_taxable_rials' => $totalTaxable,
                'total_vat_rials' => $totalVat,
                'invoices' => $invoices,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }

        $this->line(sprintf('  <fg=cyan>• Invoices Processed:</>  <fg=white>%d</>', count($invoices)));
        $this->line(sprintf('  <fg=cyan>• Total Taxable Base:</>  <fg=white>%s Rials</>', number_format($totalTaxable)));
        $this->line(sprintf('  <fg=cyan>• Total VAT Payable:</>   <fg=green>%s Rials</>', number_format($totalVat)));
        $this->line(sprintf('  <fg=cyan>• Export Destination:</>  <fg=yellow>%s</>', $outputPath));
        $this->newLine();
        $this->info('✔ Moadian tax package successfully exported and ready for TSP submission.');

        return self::SUCCESS;
    }
}
