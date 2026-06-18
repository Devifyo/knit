<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: null });
defineProps({ company: Object, consentText: String });
</script>

<template>
    <Head title="Privacy Policy — Knit" />

    <div class="min-h-screen bg-white text-slate-900 antialiased">
        <header class="border-b border-slate-200/70 bg-white">
            <div class="mx-auto flex h-16 max-w-3xl items-center justify-between px-5 sm:px-8">
                <Link href="/contact" class="flex items-center gap-2">
                    <span class="grid size-8 place-items-center rounded-lg bg-indigo-600 text-sm font-bold text-white">K</span>
                    <span class="text-lg font-semibold tracking-tight">{{ company.name }}</span>
                </Link>
                <Link href="/contact" class="text-sm font-medium text-indigo-600 hover:underline">← Back to site</Link>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-5 py-12 sm:px-8 sm:py-16">
            <h1 class="text-3xl font-bold tracking-tight">Privacy Policy</h1>
            <p class="mt-2 text-sm text-slate-500">Last updated: June 2026</p>

            <div class="mt-8 space-y-8 text-[15px] leading-relaxed text-slate-700">
                <section>
                    <p>This Privacy Policy explains how {{ company.legalName }} ("{{ company.name }}", "we", "us") collects, uses and protects your personal information when you contact us or request a consultation through {{ company.website }}.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">1. Information we collect</h2>
                    <p class="mt-3">When you submit our consultation form, we collect the information you provide, including:</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-6">
                        <li><strong>Name</strong> — to identify and address you.</li>
                        <li><strong>Email address</strong> — to respond to your inquiry and send project-related information.</li>
                        <li><strong>Phone number</strong> — to contact you about your inquiry and, if you opt in, to send SMS communications.</li>
                        <li>Optional details you choose to share, such as your company name and project requirements.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">2. SMS communications</h2>
                    <p class="mt-3">If you check the SMS consent box on our form, you expressly opt in to receive text messages from {{ company.name }} regarding your inquiry, project updates, service information, and promotional offers. The consent you agree to reads:</p>
                    <blockquote class="mt-3 border-l-4 border-indigo-200 bg-indigo-50/60 px-4 py-3 text-sm italic text-slate-600">{{ consentText }}</blockquote>
                    <p class="mt-3">Message frequency varies. Message and data rates may apply. Providing SMS consent is not a condition of purchasing any product or service. We do not send text messages unless you have opted in.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">3. How to opt out</h2>
                    <p class="mt-3">You can opt out of SMS messages at any time by replying <strong>STOP</strong> to any message you receive from us. After you reply STOP, we will stop sending you SMS messages. For help, reply <strong>HELP</strong> or contact us at <a :href="`mailto:${company.email}`" class="text-indigo-600 hover:underline">{{ company.email }}</a>.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">4. How we use your information</h2>
                    <p class="mt-3">We use your information solely to respond to your inquiry, provide consultations and proposals, deliver the services you request, and — with your consent — send the SMS communications described above.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">5. We do not sell your information</h2>
                    <p class="mt-3"><strong>We do not sell, rent or trade your personal information to third parties.</strong> Your phone number and SMS consent are never shared with third parties for their own marketing purposes. We may share information only with service providers who help us operate (for example, our messaging or email provider), and only to the extent needed to provide the service.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">6. Data retention &amp; security</h2>
                    <p class="mt-3">We retain your information for as long as necessary to respond to your inquiry and meet legal or compliance obligations, and we take reasonable measures to protect it.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">7. Contact us</h2>
                    <p class="mt-3">Questions about this policy? Contact us at <a :href="`mailto:${company.email}`" class="text-indigo-600 hover:underline">{{ company.email }}</a> or visit <a :href="company.website" class="text-indigo-600 hover:underline">{{ company.website }}</a>. Business hours: {{ company.hours }}.</p>
                </section>
            </div>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-3xl flex-col items-center justify-between gap-3 px-5 py-8 sm:flex-row sm:px-8">
                <p class="text-sm text-slate-500">© {{ new Date().getFullYear() }} {{ company.legalName }}.</p>
                <nav class="flex gap-6 text-sm font-medium text-slate-600">
                    <Link href="/privacy" class="hover:text-slate-900">Privacy Policy</Link>
                    <Link href="/terms" class="hover:text-slate-900">Terms &amp; Conditions</Link>
                    <Link href="/contact" class="hover:text-slate-900">Contact Us</Link>
                </nav>
            </div>
        </footer>
    </div>
</template>
