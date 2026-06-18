<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

defineOptions({ layout: null });
const props = defineProps({ company: Object, consentText: String });

const page = usePage();
const success = computed(() => page.props.flash?.success);

const form = useForm({
    name: '',
    company: '',
    email: '',
    phone: '',
    requirements: '',
    sms_consent: false, // unchecked by default — required to submit
});
const submit = () => form.post('/contact', { preserveScroll: true });

const steps = [
    { n: '01', title: 'Submit your inquiry', body: 'Tell us about your project using the form above. It takes about a minute.' },
    { n: '02', title: 'Receive a consultation', body: 'We schedule a call to understand your goals, scope and timeline.' },
    { n: '03', title: 'Get a project proposal', body: 'You receive a clear proposal with scope, milestones and pricing.' },
    { n: '04', title: 'Start development', body: 'We kick off, with regular updates and transparent communication.' },
];

const services = [
    { title: 'Custom Web Applications', body: 'Tailored web apps built around your exact business processes.', icon: 'M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7zM3 9h18' },
    { title: 'SaaS Development', body: 'Multi-tenant, subscription-ready products designed to scale.', icon: 'M12 3l9 5-9 5-9-5 9-5zM3 12l9 5 9-5M3 17l9 5 9-5' },
    { title: 'Laravel Development', body: 'Robust, maintainable backends and APIs on modern Laravel.', icon: 'M4 6h16M4 12h16M4 18h16' },
    { title: 'AI Automation', body: 'Automate workflows, support and insights with practical AI.', icon: 'M12 2a7 7 0 00-7 7c0 3 2 5 2 7h10c0-2 2-4 2-7a7 7 0 00-7-7zM9 21h6' },
    { title: 'API Development', body: 'Secure, well-documented APIs and third-party integrations.', icon: 'M8 9l-3 3 3 3M16 9l3 3-3 3M13 5l-2 14' },
    { title: 'CRM & Business Systems', body: 'Internal tools and CRMs that fit how your team actually works.', icon: 'M16 11a4 4 0 10-8 0 4 4 0 008 0zM4 21v-1a6 6 0 0112 0v1' },
];
</script>

<template>
    <Head title="Contact Knit — Request a Consultation" />

    <div class="min-h-screen bg-white text-slate-900 antialiased">
        <!-- Nav -->
        <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/80 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-5 sm:px-8">
                <a href="/contact" class="flex items-center gap-2">
                    <span class="grid size-8 place-items-center rounded-lg bg-indigo-600 text-sm font-bold text-white">K</span>
                    <span class="text-lg font-semibold tracking-tight">{{ company.name }}</span>
                </a>
                <nav class="hidden items-center gap-7 text-sm font-medium text-slate-600 sm:flex">
                    <a href="#services" class="hover:text-slate-900">Services</a>
                    <a href="#how" class="hover:text-slate-900">How it works</a>
                    <a href="#contact" class="hover:text-slate-900">Contact</a>
                </nav>
                <a href="#form" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">Request a Consultation</a>
            </div>
        </header>

        <!-- Hero -->
        <section class="relative overflow-hidden">
            <div class="pointer-events-none absolute inset-0 bg-gradient-to-b from-indigo-50 to-white" />
            <div class="relative mx-auto max-w-6xl px-5 py-20 sm:px-8 sm:py-28">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">Software development &amp; AI automation</span>
                    <h1 class="mt-5 text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl">Let's Build Your Next Software Project</h1>
                    <p class="mx-auto mt-5 max-w-2xl text-lg leading-relaxed text-slate-600">
                        {{ company.name }} designs and builds custom web applications, SaaS platforms, and AI automation.
                        From Laravel development and API integrations to business workflow automation, we turn your
                        requirements into reliable, scalable software.
                    </p>
                    <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                        <a href="#form" class="w-full rounded-lg bg-indigo-600 px-6 py-3 text-center text-base font-semibold text-white shadow-sm transition hover:bg-indigo-700 sm:w-auto">Request a Consultation</a>
                        <a href="#services" class="w-full rounded-lg border border-slate-300 bg-white px-6 py-3 text-center text-base font-semibold text-slate-700 transition hover:bg-slate-50 sm:w-auto">Explore services</a>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">No obligation. We typically respond within one business day.</p>
                </div>
            </div>
        </section>

        <!-- Form -->
        <section id="form" class="scroll-mt-20 bg-slate-50 py-16 sm:py-20">
            <div class="mx-auto grid max-w-6xl gap-10 px-5 sm:px-8 lg:grid-cols-2 lg:gap-16">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Request a consultation</h2>
                    <p class="mt-3 text-slate-600">Share a few details about your project and we'll get back to you to plan the next steps.</p>
                    <ul class="mt-6 space-y-3 text-sm text-slate-600">
                        <li v-for="t in ['Free, no-obligation consultation', 'Clear proposal with scope &amp; pricing', 'Direct line to senior engineers']" :key="t" class="flex items-start gap-2.5">
                            <svg viewBox="0 0 24 24" fill="none" class="mt-0.5 size-5 shrink-0 text-indigo-600" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            <span v-html="t" />
                        </li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <!-- Success state -->
                    <div v-if="success" class="py-8 text-center">
                        <div class="mx-auto grid size-12 place-items-center rounded-full bg-green-100 text-green-600">
                            <svg viewBox="0 0 24 24" fill="none" class="size-7" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold">Request received</h3>
                        <p class="mt-1 text-slate-600">{{ success }}</p>
                    </div>

                    <form v-else class="space-y-4" @submit.prevent="submit">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Full name <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" autocomplete="name" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Company name</label>
                            <input v-model="form.company" type="text" autocomplete="organization" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Email address <span class="text-red-500">*</span></label>
                                <input v-model="form.email" type="email" autocomplete="email" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                                <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Phone number <span class="text-red-500">*</span></label>
                                <input v-model="form.phone" type="tel" autocomplete="tel" placeholder="+1 555 123 4567" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200" />
                                <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Project requirements</label>
                            <textarea v-model="form.requirements" rows="4" placeholder="Tell us what you're looking to build…" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200"></textarea>
                        </div>

                        <!-- SMS consent — unchecked by default, required -->
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <label class="flex gap-3">
                                <input v-model="form.sms_consent" type="checkbox" class="mt-0.5 size-4 shrink-0 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                                <span class="text-xs leading-relaxed text-slate-600">{{ consentText }}</span>
                            </label>
                            <p v-if="form.errors.sms_consent" class="mt-2 text-xs font-medium text-red-600">{{ form.errors.sms_consent }}</p>
                        </div>

                        <button type="submit" :disabled="form.processing" class="w-full rounded-lg bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-60">
                            {{ form.processing ? 'Submitting…' : 'Request a Consultation' }}
                        </button>
                        <p class="text-center text-xs text-slate-500">
                            By submitting, you agree to our
                            <Link href="/privacy" class="text-indigo-600 hover:underline">Privacy Policy</Link> and
                            <Link href="/terms" class="text-indigo-600 hover:underline">Terms &amp; Conditions</Link>.
                        </p>
                    </form>
                </div>
            </div>
        </section>

        <!-- How it works -->
        <section id="how" class="scroll-mt-20 py-16 sm:py-20">
            <div class="mx-auto max-w-6xl px-5 sm:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">How it works</h2>
                    <p class="mt-3 text-slate-600">A simple, transparent path from idea to working software.</p>
                </div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="s in steps" :key="s.n" class="rounded-2xl border border-slate-200 bg-white p-6">
                        <span class="text-sm font-bold text-indigo-600">{{ s.n }}</span>
                        <h3 class="mt-2 text-base font-semibold">{{ s.title }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ s.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services -->
        <section id="services" class="scroll-mt-20 bg-slate-50 py-16 sm:py-20">
            <div class="mx-auto max-w-6xl px-5 sm:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">What we build</h2>
                    <p class="mt-3 text-slate-600">End-to-end software and automation, delivered by a senior team.</p>
                </div>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="svc in services" :key="svc.title" class="rounded-2xl border border-slate-200 bg-white p-6 transition hover:shadow-md">
                        <div class="grid size-11 place-items-center rounded-xl bg-indigo-50 text-indigo-600">
                            <svg viewBox="0 0 24 24" fill="none" class="size-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path :d="svc.icon" /></svg>
                        </div>
                        <h3 class="mt-4 text-base font-semibold">{{ svc.title }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-slate-600">{{ svc.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact information -->
        <section id="contact" class="scroll-mt-20 py-16 sm:py-20">
            <div class="mx-auto max-w-6xl px-5 sm:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Get in touch</h2>
                    <p class="mt-3 text-slate-600">Prefer email? Reach us directly — we're happy to help.</p>
                </div>
                <div class="mx-auto mt-10 grid max-w-3xl gap-6 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Support email</p>
                        <a :href="`mailto:${company.email}`" class="mt-2 block break-words font-medium text-indigo-600 hover:underline">{{ company.email }}</a>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Website</p>
                        <a :href="company.website" class="mt-2 block break-words font-medium text-indigo-600 hover:underline">{{ company.website }}</a>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Business hours</p>
                        <p class="mt-2 font-medium text-slate-700">{{ company.hours }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 px-5 py-8 sm:flex-row sm:px-8">
                <p class="text-sm text-slate-500">© {{ new Date().getFullYear() }} {{ company.legalName }}. All rights reserved.</p>
                <nav class="flex items-center gap-6 text-sm font-medium text-slate-600">
                    <Link href="/privacy" class="hover:text-slate-900">Privacy Policy</Link>
                    <Link href="/terms" class="hover:text-slate-900">Terms &amp; Conditions</Link>
                    <a href="#form" class="hover:text-slate-900">Contact Us</a>
                </nav>
            </div>
        </footer>
    </div>
</template>
