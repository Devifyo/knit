<script setup>
import { computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

defineOptions({ layout: null });
const props = defineProps({ company: Object, consentText: String });

const page = usePage();
const success = computed(() => page.props.flash?.success);
const year = new Date().getFullYear();

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
    { n: '01', title: 'Submit your inquiry', body: 'Tell us about your project. It takes about a minute.' },
    { n: '02', title: 'Receive a consultation', body: 'We get on a call to understand goals, scope and timeline.' },
    { n: '03', title: 'Get a project proposal', body: 'A clear plan with scope, milestones and pricing.' },
    { n: '04', title: 'Start development', body: 'We build — with regular updates and honest communication.' },
];

const services = [
    { title: 'Custom Web Applications', body: 'Applications built around your exact processes — not a template.' },
    { title: 'SaaS Development', body: 'Multi-tenant, subscription-ready products designed to scale.' },
    { title: 'Laravel Development', body: 'Maintainable backends and APIs on modern Laravel.' },
    { title: 'AI Automation', body: 'Automate workflows, support and insight with practical AI.' },
    { title: 'API Development', body: 'Secure, documented APIs and third-party integrations.' },
    { title: 'CRM & Business Systems', body: 'Internal tools and CRMs that fit how your team works.' },
];

const field = 'w-full rounded-md border border-stone-300 bg-white px-3.5 py-2.5 text-sm text-stone-900 shadow-[inset_0_1px_0_rgba(0,0,0,0.02)] transition placeholder:text-stone-400 focus:border-stone-900 focus:outline-none focus:ring-4 focus:ring-stone-900/5';
</script>

<template>
    <Head title="Contact Knit — Request a Consultation" />

    <div class="min-h-screen bg-[#faf9f7] font-sans text-stone-900 antialiased selection:bg-stone-900 selection:text-white">
        <!-- Nav -->
        <header class="border-b border-stone-200/80">
            <div class="mx-auto flex h-[68px] max-w-6xl items-center justify-between px-5 sm:px-8">
                <a href="/contact" class="flex items-baseline gap-2.5">
                    <span class="serif text-2xl font-semibold leading-none">Knit</span>
                    <span class="hidden text-[11px] uppercase tracking-[0.2em] text-stone-400 sm:inline">{{ company.legalName }}</span>
                </a>
                <nav class="hidden items-center gap-8 text-sm text-stone-500 md:flex">
                    <a href="#services" class="transition hover:text-stone-900">Services</a>
                    <a href="#how" class="transition hover:text-stone-900">Process</a>
                    <a href="#contact" class="transition hover:text-stone-900">Contact</a>
                </nav>
                <a href="#form" class="rounded-md bg-stone-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-stone-700">Request a consultation</a>
            </div>
        </header>

        <!-- Hero + form (asymmetric, form above the fold) -->
        <section class="relative">
            <div class="pointer-events-none absolute inset-0 [background-image:radial-gradient(circle_at_1px_1px,rgba(0,0,0,0.05)_1px,transparent_0)] [background-size:22px_22px] [mask-image:linear-gradient(to_bottom,black,transparent_70%)]" />
            <div class="relative mx-auto grid max-w-6xl gap-14 px-5 py-16 sm:px-8 sm:py-24 lg:grid-cols-[1.05fr_0.95fr] lg:gap-20">
                <!-- Copy -->
                <div class="max-w-xl lg:pt-6">
                    <p class="eyebrow text-[12px] text-stone-500">Software development &amp; AI automation</p>
                    <h1 class="serif mt-5 text-[2.7rem] font-medium leading-[1.04] tracking-[-0.02em] text-stone-900 sm:text-6xl">
                        Let's build your next<br class="hidden sm:block" /> software project.
                    </h1>
                    <p class="mt-6 text-lg leading-relaxed text-stone-600">
                        We design and build custom web applications, SaaS platforms and AI automation — from
                        Laravel development and API integrations to business workflow automation. You bring the
                        problem; we ship reliable, scalable software.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3">
                        <a href="#form" class="rounded-md bg-stone-900 px-6 py-3 text-base font-medium text-white transition hover:bg-stone-700">Request a consultation</a>
                        <a href="#services" class="text-base font-medium text-stone-900 underline decoration-stone-300 decoration-1 underline-offset-[5px] transition hover:decoration-stone-900">See what we build</a>
                    </div>
                    <div class="mt-10 flex flex-wrap items-center gap-x-7 gap-y-2 border-t border-stone-200 pt-6 text-sm text-stone-500">
                        <span>Senior engineers</span>
                        <span class="hidden sm:inline text-stone-300">·</span>
                        <span>Fixed-scope proposals</span>
                        <span class="hidden sm:inline text-stone-300">·</span>
                        <span>Reply within one business day</span>
                    </div>
                </div>

                <!-- Form -->
                <div id="form" class="scroll-mt-24">
                    <div class="rounded-2xl border border-stone-200 bg-white p-6 shadow-[0_1px_2px_rgba(0,0,0,0.04),0_12px_40px_-12px_rgba(0,0,0,0.12)] sm:p-8">
                        <template v-if="success">
                            <div class="py-10 text-center">
                                <div class="mx-auto grid size-12 place-items-center rounded-full bg-stone-900 text-white">
                                    <svg viewBox="0 0 24 24" fill="none" class="size-6" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                </div>
                                <h3 class="serif mt-5 text-2xl font-medium">Request received</h3>
                                <p class="mx-auto mt-2 max-w-xs text-stone-600">{{ success }}</p>
                            </div>
                        </template>

                        <template v-else>
                            <p class="eyebrow text-[11px] text-stone-400">Consultation request</p>
                            <h2 class="serif mt-2 text-2xl font-medium">Tell us about your project</h2>

                            <form class="mt-6 space-y-4" @submit.prevent="submit">
                                <div>
                                    <label class="mb-1.5 block text-[13px] font-medium text-stone-700">Full name <span class="text-stone-400">*</span></label>
                                    <input v-model="form.name" type="text" autocomplete="name" :class="field" />
                                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-[13px] font-medium text-stone-700">Company name</label>
                                    <input v-model="form.company" type="text" autocomplete="organization" :class="field" />
                                </div>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-[13px] font-medium text-stone-700">Email <span class="text-stone-400">*</span></label>
                                        <input v-model="form.email" type="email" autocomplete="email" :class="field" />
                                        <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-[13px] font-medium text-stone-700">Phone <span class="text-stone-400">*</span></label>
                                        <input v-model="form.phone" type="tel" autocomplete="tel" placeholder="+1 555 123 4567" :class="field" />
                                        <p v-if="form.errors.phone" class="mt-1 text-xs text-red-600">{{ form.errors.phone }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-[13px] font-medium text-stone-700">Project requirements</label>
                                    <textarea v-model="form.requirements" rows="4" placeholder="What are you looking to build?" :class="field"></textarea>
                                </div>

                                <!-- SMS consent — unchecked by default, required -->
                                <label class="flex gap-3 rounded-lg bg-stone-50 p-3.5 ring-1 ring-inset ring-stone-200">
                                    <input v-model="form.sms_consent" type="checkbox" class="mt-0.5 size-4 shrink-0 rounded border-stone-400 text-stone-900 focus:ring-stone-900" />
                                    <span class="text-[12px] leading-relaxed text-stone-600">{{ consentText }}</span>
                                </label>
                                <p v-if="form.errors.sms_consent" class="-mt-1 text-xs font-medium text-red-600">{{ form.errors.sms_consent }}</p>

                                <button type="submit" :disabled="form.processing" class="w-full rounded-md bg-stone-900 px-6 py-3 text-base font-medium text-white transition hover:bg-stone-700 disabled:opacity-60">
                                    {{ form.processing ? 'Submitting…' : 'Request a consultation' }}
                                </button>
                                <p class="text-center text-[12px] text-stone-400">
                                    By submitting you agree to our
                                    <Link href="/privacy" class="text-stone-600 underline decoration-stone-300 underline-offset-2 hover:text-stone-900">Privacy Policy</Link>
                                    and
                                    <Link href="/terms" class="text-stone-600 underline decoration-stone-300 underline-offset-2 hover:text-stone-900">Terms</Link>.
                                </p>
                            </form>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <!-- Process -->
        <section id="how" class="scroll-mt-20 border-t border-stone-200 bg-white">
            <div class="mx-auto max-w-6xl px-5 py-20 sm:px-8">
                <p class="eyebrow text-[12px] text-stone-400">How it works</p>
                <h2 class="serif mt-3 max-w-xl text-3xl font-medium tracking-[-0.01em] sm:text-4xl">A clear path from idea to shipped software.</h2>
                <div class="mt-12 grid gap-px overflow-hidden rounded-2xl border border-stone-200 bg-stone-200 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="s in steps" :key="s.n" class="bg-white p-7">
                        <span class="serif text-3xl font-medium text-stone-300">{{ s.n }}</span>
                        <h3 class="mt-3 text-base font-semibold text-stone-900">{{ s.title }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-stone-500">{{ s.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Services -->
        <section id="services" class="scroll-mt-20 border-t border-stone-200">
            <div class="mx-auto max-w-6xl px-5 py-20 sm:px-8">
                <p class="eyebrow text-[12px] text-stone-400">Capabilities</p>
                <h2 class="serif mt-3 max-w-xl text-3xl font-medium tracking-[-0.01em] sm:text-4xl">What we build.</h2>
                <div class="mt-12 grid gap-x-12 gap-y-10 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="(svc, i) in services" :key="svc.title" class="border-t border-stone-900/10 pt-5">
                        <span class="eyebrow text-[11px] text-stone-400">{{ String(i + 1).padStart(2, '0') }}</span>
                        <h3 class="serif mt-2 text-xl font-medium text-stone-900">{{ svc.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-stone-500">{{ svc.body }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact info -->
        <section id="contact" class="scroll-mt-20 border-t border-stone-200 bg-stone-900 text-stone-100">
            <div class="mx-auto max-w-6xl px-5 py-20 sm:px-8">
                <div class="grid gap-12 lg:grid-cols-[1fr_auto]">
                    <div class="max-w-md">
                        <p class="eyebrow text-[12px] text-stone-500">Get in touch</p>
                        <h2 class="serif mt-3 text-3xl font-medium tracking-[-0.01em] sm:text-4xl">Prefer email? We're happy to help.</h2>
                        <a href="#form" class="mt-7 inline-block rounded-md bg-white px-6 py-3 text-base font-medium text-stone-900 transition hover:bg-stone-200">Start a request</a>
                    </div>
                    <dl class="grid grid-cols-1 gap-8 sm:grid-cols-3 lg:gap-12">
                        <div>
                            <dt class="eyebrow text-[11px] text-stone-500">Support email</dt>
                            <dd class="mt-2"><a :href="`mailto:${company.email}`" class="break-words text-stone-100 underline decoration-stone-600 underline-offset-4 hover:decoration-stone-100">{{ company.email }}</a></dd>
                        </div>
                        <div>
                            <dt class="eyebrow text-[11px] text-stone-500">Website</dt>
                            <dd class="mt-2"><a :href="company.website" class="break-words text-stone-100 underline decoration-stone-600 underline-offset-4 hover:decoration-stone-100">{{ company.website }}</a></dd>
                        </div>
                        <div>
                            <dt class="eyebrow text-[11px] text-stone-500">Business hours</dt>
                            <dd class="mt-2 text-stone-300">{{ company.hours }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-stone-900 text-stone-400">
            <div class="mx-auto flex max-w-6xl flex-col items-center justify-between gap-4 border-t border-white/10 px-5 py-8 sm:flex-row sm:px-8">
                <p class="text-sm">© {{ year }} {{ company.legalName }}. All rights reserved.</p>
                <nav class="flex items-center gap-7 text-sm">
                    <Link href="/privacy" class="hover:text-white">Privacy Policy</Link>
                    <Link href="/terms" class="hover:text-white">Terms &amp; Conditions</Link>
                    <a href="#form" class="hover:text-white">Contact Us</a>
                </nav>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.serif { font-family: 'Fraunces', Georgia, 'Times New Roman', serif; font-optical-sizing: auto; }
.eyebrow { font-family: 'Geist Mono', ui-monospace, SFMono-Regular, monospace; text-transform: uppercase; letter-spacing: 0.2em; font-weight: 500; }
</style>
