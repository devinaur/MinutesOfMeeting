<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('finance-access', fn (User $user) => $user->isFinance() || $user->isAdmin());
        Gate::define('finance-or-admin', fn (User $user) => $user->isFinance() || $user->isAdmin());

        // Carbon macros for UTC to WIB (Asia/Jakarta, UTC+7) presentation
        Carbon::macro('toWib', function () {
            /** @var Carbon $this */
            return $this->copy()->setTimezone('Asia/Jakarta');
        });

        Carbon::macro('formatWib', function (string $format = 'd M Y, H:i') {
            /** @var Carbon $this */
            return $this->copy()->setTimezone('Asia/Jakarta')->format($format);
        });

        Blade::directive('wib', function ($expression) {
            return "<?php echo ({$expression}) ? ({$expression})->timezone('Asia/Jakarta')->format('d M Y, H:i') : '-'; ?>";
        });
    }
}
