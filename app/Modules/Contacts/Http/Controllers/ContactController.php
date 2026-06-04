<?php

declare(strict_types=1);

namespace App\Modules\Contacts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Company;
use App\Models\Contact;
use App\Models\CustomFieldDefinition;
use App\Models\Deal;
use App\Modules\Portal\Mail\PortalAccessMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(Request $request): Response
    {
        $search = (string) $request->string('search');

        $contacts = Contact::with('company:id,name')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (Contact $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'email' => $c->email,
                'job_title' => $c->job_title,
                'company' => $c->company?->name,
                'lifecycle_stage' => $c->lifecycle_stage,
            ])->all();

        return Inertia::render('Contacts/Index', [
            'contacts' => $contacts,
            'search' => $search,
            'customFields' => $this->fields(),
            'companies' => Company::orderBy('name')->limit(200)->get(['id', 'name']),
        ]);
    }

    public function show(Contact $contact): Response
    {
        $contact->load(['company:id,name', 'owner:id,name', 'tags', 'activities.user:id,name', 'deals.quotes', 'deals.stage:id,name']);

        return Inertia::render('Contacts/Show', [
            'contact' => [
                'id' => $contact->id,
                'name' => $contact->name,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'job_title' => $contact->job_title,
                'lifecycle_stage' => $contact->lifecycle_stage,
                'source' => $contact->source,
                'company' => $contact->company?->name,
                'owner' => $contact->owner?->name,
                'custom_fields' => $contact->custom_fields ?? [],
                'tags' => $contact->tags->map(fn ($t) => ['name' => $t->name, 'color' => $t->color]),
                'deals' => $contact->deals->map(fn (Deal $d) => [
                    'id' => $d->id,
                    'name' => $d->name,
                    'amount' => $d->formattedAmount(),
                    'stage' => $d->stage?->name,
                    'status' => $d->status,
                    'quotes' => $d->quotes->count(),
                ]),
                'activities' => $contact->activities->map(fn (Activity $a) => [
                    'id' => $a->id,
                    'type' => $a->type,
                    'body' => $a->body,
                    'author' => $a->user?->name,
                    'at' => $a->created_at?->diffForHumans(),
                ]),
            ],
            'customFields' => $this->fields(),
            'portal' => [
                'enabled' => (bool) $contact->portal_enabled,
                'activated' => $contact->portal_activated_at !== null,
                'activation_url' => $contact->portal_token ? url('/portal/activate/'.$contact->portal_token) : null,
                'login_url' => url('/portal/login'),
                'has_email' => (bool) $contact->email,
            ],
        ]);
    }

    /** Enable / disable / reset a contact's customer-portal access. */
    public function portalAccess(Request $request, Contact $contact): RedirectResponse
    {
        $action = (string) $request->input('action');

        if ($action === 'disable') {
            $contact->forceFill(['portal_enabled' => false])->save();

            return back()->with('success', 'Portal access disabled.');
        }

        if (! $contact->email) {
            return back()->with('error', 'Add an email address before enabling portal access.');
        }

        if ($action === 'reset') {
            // Force a fresh set-password link (e.g. the customer forgot their password).
            $contact->forceFill([
                'portal_enabled' => true,
                'portal_token' => Str::random(48),
                'portal_activated_at' => null,
            ])->save();
        } else {
            $contact->forceFill(['portal_enabled' => true]);
            if ($contact->portal_activated_at === null && ! $contact->portal_token) {
                $contact->portal_token = Str::random(48);
            }
            $contact->save();
        }

        // Email the set-password link when there's one to send.
        if ($contact->portal_token) {
            try {
                Mail::to($contact->email)->send(new PortalAccessMail(
                    url('/portal/activate/'.$contact->portal_token),
                    (string) (tenant('name') ?? 'your workspace'),
                    reset: $action === 'reset',
                ));

                return back()->with('success', "Portal invite emailed to {$contact->email}.");
            } catch (\Throwable) {
                return back()->with('success', 'Portal enabled — email delivery failed, copy the activation link from the card.');
            }
        }

        return back()->with('success', 'Portal access enabled.');
    }

    /**
     * Sign in to the customer portal AS this contact (impersonation) to see
     * exactly what they see. Staff stay logged into the app (web guard); only the
     * separate `contact` guard is logged in, marked so the portal shows an exit
     * banner. Permission-gated.
     */
    public function impersonate(Request $request, Contact $contact): RedirectResponse
    {
        abort_unless($request->user()->can('contacts.impersonate'), 403);

        Auth::guard('contact')->login($contact);
        $request->session()->put('impersonator_user_id', $request->user()->id);
        $request->session()->put('impersonate_return', "/contacts/{$contact->id}");

        return redirect('/portal');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'company_id' => ['nullable', 'exists:companies,id'],
            'custom_fields' => ['nullable', 'array'],
        ]);

        // Duplicate detection on email within the workspace.
        if (! empty($data['email']) && Contact::where('email', $data['email'])->exists()) {
            return back()->withErrors(['email' => 'A contact with this email already exists.']);
        }

        Contact::create([...$data, 'owner_id' => $request->user()->id]);

        return back()->with('success', 'Contact created.');
    }

    public function addNote(Request $request, Contact $contact): RedirectResponse
    {
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $contact->activities()->create([
            'type' => 'note',
            'body' => $data['body'],
            'user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Note added.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function fields(): array
    {
        return CustomFieldDefinition::where('entity', 'contact')->orderBy('order')->get()
            ->map(fn (CustomFieldDefinition $f) => [
                'key' => $f->key, 'label' => $f->label, 'type' => $f->type,
                'options' => $f->options, 'required' => $f->required,
            ])->all();
    }
}
