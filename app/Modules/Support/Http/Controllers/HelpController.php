<?php

declare(strict_types=1);

namespace App\Modules\Support\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use App\Models\Tenant;
use App\Services\AI\GeminiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public self-service portal: published KB articles + an AI chatbot that answers
 * from them (via the single GeminiService, with graceful fallback).
 */
class HelpController extends Controller
{
    public function show(string $slug): Response
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        tenancy()->initialize($tenant);
        try {
            $articles = KbArticle::where('published', true)->latest()->get(['title', 'slug', 'body'])
                ->map(fn (KbArticle $a): array => ['title' => $a->title, 'slug' => $a->slug, 'body' => $a->body])->all();
        } finally {
            tenancy()->end();
        }

        return Inertia::render('Help/Portal', [
            'workspace' => ['name' => $tenant->name, 'slug' => $tenant->slug, 'brand_color' => $tenant->brand_color],
            'articles' => $articles,
            'answer' => session('answer'),
            'question' => session('question'),
        ]);
    }

    public function ask(Request $request, string $slug, GeminiService $ai): RedirectResponse
    {
        $tenant = Tenant::where('slug', $slug)->firstOrFail();
        $data = $request->validate(['question' => ['required', 'string', 'max:500']]);

        tenancy()->initialize($tenant);
        try {
            $context = KbArticle::where('published', true)->get(['title', 'body'])
                ->map(fn (KbArticle $a) => $a->title.': '.$a->body)->implode("\n\n");
            $answer = $ai->chatbotReply($data['question'], ['kb' => $context]);
        } finally {
            tenancy()->end();
        }

        return back()->with([
            'question' => $data['question'],
            'answer' => $answer !== '' ? $answer : 'Thanks for your question — a support agent will follow up shortly. Meanwhile, browse the articles below.',
        ]);
    }
}
