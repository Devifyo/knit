<?php

declare(strict_types=1);

namespace App\Modules\Industry\Services;

use App\Models\ModuleSetting;

/**
 * Per-tenant enablement of industry modules. Reads/writes module_settings for
 * the current tenant and derives the navigation entries for enabled modules.
 */
class Modules
{
    public function isEnabled(string $key): bool
    {
        return ModuleSetting::where('key', $key)->where('enabled', true)->exists();
    }

    /** @return array<int, string> */
    public function enabledKeys(): array
    {
        return ModuleSetting::where('enabled', true)->pluck('key')->all();
    }

    public function setEnabled(string $key, bool $enabled): void
    {
        ModuleSetting::updateOrCreate(['key' => $key], ['enabled' => $enabled]);
    }

    /**
     * Sidebar entries for the current tenant's enabled modules.
     *
     * @return array<int, array{label: string, href: string, icon: string}>
     */
    public function navEntries(): array
    {
        $enabled = $this->enabledKeys();
        $entries = [];

        foreach (ModuleRegistry::modules() as $module) {
            if (! in_array($module['key'], $enabled, true)) {
                continue;
            }
            $entity = $module['entity'];
            $entries[] = [
                'label' => $entity['label'],
                'href' => "/m/{$module['key']}/{$entity['key']}",
                'icon' => $module['icon'],
            ];
        }

        return $entries;
    }
}
