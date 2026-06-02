<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Serves the in-app User Guide. Renders the Markdown how-to docs that live in
 * docs/guide/ as Inertia pages so every signed-in member can read them without
 * leaving the app. Read-only and tenant-agnostic (the docs are the same for all
 * workspaces), so no permission gate is applied.
 */
class GuideController extends Controller
{
    /** Where the Markdown guides live, relative to the project root. */
    private const DIR = 'docs/guide';

    /** The overview page maps to README.md but reads nicer as /guide. */
    private const OVERVIEW = 'overview';

    public function index(): Response
    {
        return $this->renderGuide(self::OVERVIEW);
    }

    public function show(string $slug): Response
    {
        return $this->renderGuide($slug);
    }

    private function renderGuide(string $slug): Response
    {
        $guides = $this->manifest();
        $file = $slug === self::OVERVIEW ? 'README' : $slug;
        $path = base_path(self::DIR.'/'.$file.'.md');

        abort_unless(isset($guides[$slug]) && is_file($path), 404);

        return Inertia::render('Guide/Show', [
            'title' => $guides[$slug]['title'],
            'current' => $slug,
            'nav' => array_values($guides),
            'html' => $this->toHtml((string) file_get_contents($path)),
        ]);
    }

    /**
     * Build the ordered list of guides (slug, title, url) from the Markdown
     * files on disk, keyed by slug. README sorts first as the overview.
     *
     * @return array<string, array{slug: string, title: string, url: string, order: int}>
     */
    private function manifest(): array
    {
        $items = [];

        foreach (glob(base_path(self::DIR.'/*.md')) ?: [] as $path) {
            $base = basename($path, '.md');
            $isOverview = $base === 'README';
            $slug = $isOverview ? self::OVERVIEW : $base;

            $items[] = [
                'slug' => $slug,
                'title' => $this->titleFrom($path, $base),
                'url' => $isOverview ? '/guide' : '/guide/'.$slug,
                'order' => $isOverview ? -1 : (int) $base,
            ];
        }

        usort($items, fn ($a, $b) => $a['order'] <=> $b['order']);

        $keyed = [];
        foreach ($items as $item) {
            $keyed[$item['slug']] = $item;
        }

        return $keyed;
    }

    /** Pull the first H1 as the title; clean a leading "N. " number prefix. */
    private function titleFrom(string $path, string $base): string
    {
        if ($base === 'README') {
            return 'Overview';
        }

        $title = '';
        foreach (file($path) ?: [] as $line) {
            if (str_starts_with($line, '# ')) {
                $title = trim(substr($line, 2));
                break;
            }
        }

        if ($title === '') {
            $title = Str::headline($base);
        }

        return (string) preg_replace('/^\d+\.\s*/', '', $title);
    }

    /** Convert Markdown to HTML and rewrite inter-guide .md links to /guide routes. */
    private function toHtml(string $markdown): string
    {
        $html = Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return (string) preg_replace_callback(
            '/href="([^"#]+)\.md(#[^"]*)?"/i',
            function (array $m): string {
                $file = basename($m[1]);
                $anchor = $m[2] ?? '';
                $url = $file === 'README' ? '/guide' : '/guide/'.$file;

                return 'href="'.$url.$anchor.'"';
            },
            $html,
        );
    }
}
