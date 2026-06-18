<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: null });
defineProps({ company: Object, consentText: String });
</script>

<template>
    <Head title="Terms & Conditions — Knit" />

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
            <h1 class="text-3xl font-bold tracking-tight">Terms &amp; Conditions</h1>
            <p class="mt-2 text-sm text-slate-500">Last updated: June 2026</p>

            <div class="mt-8 space-y-8 text-[15px] leading-relaxed text-slate-700">
                <section>
                    <p>These Terms &amp; Conditions govern your use of the {{ company.legalName }} ("{{ company.name }}") website and your participation in our SMS messaging program. By submitting our contact form and opting in, you agree to these terms.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">1. SMS messaging program</h2>
                    <p class="mt-3">By providing your phone number and checking the SMS consent box, you expressly consent to receive SMS text messages from {{ company.name }} regarding your inquiry, project updates, service information, and promotional offers. The consent you agree to reads:</p>
                    <blockquote class="mt-3 border-l-4 border-indigo-200 bg-indigo-50/60 px-4 py-3 text-sm italic text-slate-600">{{ consentText }}</blockquote>
                    <p class="mt-3">Consent is not a condition of purchasing any goods or services.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">2. Message frequency</h2>
                    <p class="mt-3"><strong>Message frequency varies</strong> depending on your inquiry and your interaction with us.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">3. Message &amp; data rates</h2>
                    <p class="mt-3"><strong>Message and data rates may apply</strong>, depending on your mobile carrier and plan. {{ company.name }} is not responsible for any charges from your carrier.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">4. Opting out — reply STOP</h2>
                    <p class="mt-3">You can cancel the SMS service at any time by texting <strong>STOP</strong> to any message you receive. After you send STOP, we will send a confirmation message and then stop sending SMS messages. To opt in again, submit the consent form once more.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">5. Help — reply HELP</h2>
                    <p class="mt-3">If you experience any issues or need assistance, reply <strong>HELP</strong> to any message, or email us at <a :href="`mailto:${company.email}`" class="text-indigo-600 hover:underline">{{ company.email }}</a>.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">6. Carriers &amp; privacy</h2>
                    <p class="mt-3">Carriers are not liable for delayed or undelivered messages. We handle your information in accordance with our <Link href="/privacy" class="text-indigo-600 hover:underline">Privacy Policy</Link>, and we do not sell your information to third parties.</p>
                </section>

                <section>
                    <h2 class="text-xl font-semibold text-slate-900">7. Company contact information</h2>
                    <ul class="mt-3 space-y-1.5">
                        <li><strong>Company:</strong> {{ company.legalName }}</li>
                        <li><strong>Email:</strong> <a :href="`mailto:${company.email}`" class="text-indigo-600 hover:underline">{{ company.email }}</a></li>
                        <li><strong>Website:</strong> <a :href="company.website" class="text-indigo-600 hover:underline">{{ company.website }}</a></li>
                        <li><strong>Business hours:</strong> {{ company.hours }}</li>
                    </ul>
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
