<?php

declare(strict_types=1);

namespace DigitalTunnel\SecureCode\Providers;

use DigitalTunnel\SecureCode\Console\GenerateCommand;
use DigitalTunnel\SecureCode\SecureCode;
use DigitalTunnel\SecureCode\Sequence\SequenceGenerator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class SecureCodeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/secure-code.php', 'secure-code');

        $this->app->singleton(SecureCode::class, fn () => new SecureCode);

        $this->app->singleton(SequenceGenerator::class, fn () => new SequenceGenerator);
    }

    public function boot(): void
    {
        // Blade directive: @securecode(6, 'numeric')
        Blade::directive('securecode', function (string $expression) {
            return "<?php echo \\DigitalTunnel\\SecureCode\\SecureCode::generate({$expression}); ?>";
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/secure-code.php' => config_path('secure-code.php'),
            ], 'secure-code-config');

            $this->publishesMigrations([
                __DIR__.'/../../database/migrations' => database_path('migrations'),
            ], 'secure-code-migrations');

            $this->commands([
                GenerateCommand::class,
            ]);
        }
    }
}
