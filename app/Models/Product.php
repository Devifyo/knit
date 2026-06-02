<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\Tenancy\BelongsToTenant;
use App\Support\Tenancy\TenantOwned;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model implements TenantOwned
{
    /** @use HasFactory<ProductFactory> */
    use BelongsToTenant, HasFactory;

    /** @var list<string> */
    protected $fillable = ['name', 'sku', 'description', 'unit_price', 'currency', 'active'];

    /** @var array<string, string> */
    protected $casts = ['unit_price' => 'integer', 'active' => 'boolean'];
}
