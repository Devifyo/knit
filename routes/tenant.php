<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| We run single-database scoped tenancy and resolve the tenant via the
| App\Http\Middleware\ResolveTenant middleware applied in routes/web.php.
| All workspace routes therefore live in web.php; this file is intentionally
| empty so it doesn't register duplicate/conflicting routes.
|
*/
