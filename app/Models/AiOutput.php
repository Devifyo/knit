<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Audit row for an AI result. Not tenant-scoped via the global scope (the writer
 * sets tenant_id explicitly from context) so it can be written from queued jobs.
 *
 * @property string|null $tenant_id
 * @property string $feature
 */
class AiOutput extends Model
{
    /** @var list<string> */
    protected $fillable = ['tenant_id', 'feature', 'entity_type', 'entity_id', 'prompt_hash', 'response', 'tokens'];
}
