<?php

declare(strict_types=1);

namespace App\Modules\Industry\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ModuleRecord;
use App\Modules\Industry\Services\ModuleRegistry;
use App\Modules\Industry\Services\Modules;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ModuleController extends Controller
{
    public function __construct(private readonly Modules $modules) {}

    /** Marketplace — available industry modules and their on/off state. */
    public function index(): Response
    {
        $enabled = $this->modules->enabledKeys();

        return Inertia::render('Settings/Modules', [
            'modules' => collect(ModuleRegistry::modules())->map(fn (array $m): array => [
                'key' => $m['key'],
                'name' => $m['name'],
                'description' => $m['description'],
                'icon' => $m['icon'],
                'hipaa' => (bool) ($m['hipaa'] ?? false),
                'entity' => $m['entity']['label'],
                'href' => "/m/{$m['key']}/{$m['entity']['key']}",
                'enabled' => in_array($m['key'], $enabled, true),
                'records' => in_array($m['key'], $enabled, true)
                    ? ModuleRecord::where('module_key', $m['key'])->count()
                    : 0,
            ]),
        ]);
    }

    public function toggle(string $key): RedirectResponse
    {
        $module = ModuleRegistry::find($key);
        abort_if($module === null, 404);

        $enable = ! $this->modules->isEnabled($key);
        $this->modules->setEnabled($key, $enable);

        return back()->with('success', $module['name'].' '.($enable ? 'enabled' : 'disabled').'.');
    }
}
