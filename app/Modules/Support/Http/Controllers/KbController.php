<?php

declare(strict_types=1);

namespace App\Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class KbController extends Controller
{
    public function index(): Response
    {
        $articles = KbArticle::latest()->get(['id', 'title', 'slug', 'published'])
            ->map(fn (KbArticle $a): array => [
                'id' => $a->id, 'title' => $a->title, 'slug' => $a->slug, 'published' => $a->published,
            ])->all();

        return Inertia::render('Kb/Index', ['articles' => $articles, 'portalUrl' => url('/help/'.tenant('slug'))]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        KbArticle::create([
            'title' => $data['title'],
            'slug' => Str::slug($data['title']).'-'.Str::lower(Str::random(4)),
            'body' => $data['body'],
            'published' => true,
        ]);

        return back()->with('success', 'Article published.');
    }

    public function destroy(KbArticle $article): RedirectResponse
    {
        $article->delete();

        return back()->with('success', 'Article deleted.');
    }
}
