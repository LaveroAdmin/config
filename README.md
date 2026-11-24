# Config manager for LaveroAdmin

Database configuration manager for LaveroAdmin.

Repository: [https://github.com/LaveroAdmin/config](https://github.com/LaveroAdmin/config)

## Installation

```bash
composer require laveroadmin/config

php artisan migrate

php artisan admin:import config
```

In `app/Providers/AppServiceProvider.php`, call `Config::load()` within the `boot` method:

```php
namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use LaveroAdmin\Admin\Config\Config;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $table = config('admin.extensions.config.table', 'admin_config');
        if (Schema::hasTable($table)) {
            Config::load();
        }
    }
}
```

Open `/admin/config` in your browser.

## Usage

After adding settings in the admin panel, retrieve them anywhere using Laravel's native `config('key')`.

## License

Licensed under [The MIT License (MIT)](LICENSE).

## Thanks

- Special thanks to [open-admin](https://github.com/open-admin-org/open-admin) for open-admin development.
- Special thanks to [z-song](https://github.com/z-song) for original development.
