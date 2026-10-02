<?php

declare(strict_types=1);

namespace Reyhan\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class MakeShippingDriverCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reyhan:make:shipping-driver {name : The name of the logistics carrier, e.g. Chapar or Alopeyk}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new logistics and courier driver for Reyhan Commerce';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $rawName = (string) $this->argument('name');
        $studlyName = Str::studly(str_replace(['shipping', 'driver', 'Driver'], '', $rawName));
        $driverClass = "{$studlyName}ShippingDriver";
        $driverPath = app_path("Services/Shipping/Drivers/{$driverClass}.php");

        if (File::exists($driverPath)) {
            $this->error("✖ Shipping driver class already exists at: {$driverPath}");

            return self::FAILURE;
        }

        File::ensureDirectoryExists(app_path('Services/Shipping/Drivers'));

        $driverContent = <<<PHP
<?php

declare(strict_types=1);

namespace Reyhan\Core\Services\Shipping\Drivers;

use Reyhan\Core\Models\Address;
use Reyhan\Core\Models\Cart;
use Reyhan\Core\Services\Shipping\Contracts\ShippingDriverInterface;

final class {$driverClass} implements ShippingDriverInterface
{
    /**
     * Calculate shipping rate based on cart items, weight, and destination address.
     */
    public function calculateFee(Cart \$cart, ?Address \$address = null): int
    {
        // TODO: Calculate dynamic tariff or call carrier calculation API
        return 450000; // 45,000 Tomans default
    }

    /**
     * Validate the carrier's tracking code format.
     */
    public function validateTrackingCode(string \$trackingCode): bool
    {
        // TODO: Validate tracking code regex
        return ! empty(\$trackingCode);
    }

    /**
     * Generate the public tracking web URL for customer status tracking.
     */
    public function getTrackingUrl(string \$trackingCode): ?string
    {
        return "https://tracking.carrier.ir/track/{\$trackingCode}";
    }
}
PHP;

        File::put($driverPath, $driverContent);

        $this->newLine();
        $this->line("<fg=green;options=bold>✔ Shipping Driver [{$driverClass}] generated successfully!</>");
        $this->line("<fg=gray>Location:</> <fg=yellow>{$driverPath}</>");
        $this->newLine();

        return self::SUCCESS;
    }
}
