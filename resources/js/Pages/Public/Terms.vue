<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: null });
defineProps({ company: Object, consentText: String });
const year = new Date().getFullYear();
</script>

<template>
    <Head title="Terms & Conditions — Knit" />

    <div class="min-h-screen bg-[#faf9f7] font-sans text-stone-900 antialiased selection:bg-stone-900 selection:text-white">
        <header class="border-b border-stone-200/80">
            <div class="mx-auto flex h-[68px] max-w-3xl items-center justify-between px-5 sm:px-8">
                <Link href="/contact" class="flex items-baseline gap-2.5">
                    <span class="serif text-2xl font-semibold leading-none">Knit</span>
                    <span class="hidden text-[11px] uppercase tracking-[0.2em] text-stone-400 sm:inline">{{ company.legalName }}</span>
                </Link>
                <Link href="/contact" class="text-sm text-stone-500 transition hover:text-stone-900">← Back to site</Link>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-5 py-14 sm:px-8 sm:py-20">
            <p class="eyebrow text-[12px] text-stone-400">Legal</p>
            <h1 class="serif mt-3 text-4xl font-medium tracking-[-0.02em] sm:text-5xl">Terms &amp; Conditions</h1>
            <p class="mt-3 text-sm text-stone-500">Last updated: June 2026</p>

            <div class="mt-10 space-y-9 text-[15px] leading-relaxed text-stone-700">
                <p>These Terms &amp; Conditions govern your use of the {{ company.legalName }} ("{{ company.name }}") website and your participation in our SMS messaging program. By submitting our contact form and opting in, you agree to these terms.</p>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">1. SMS messaging program</h2>
                    <p class="mt-3">By providing your phone number and checking the SMS consent box, you expressly consent to receive SMS text messages from {{ company.name }} regarding your inquiry, project updates, service information, and promotional offers. The consent you agree to reads:</p>
                    <blockquote class="mt-4 border-l-2 border-stone-900/20 pl-4 text-sm italic text-stone-500">{{ consentText }}</blockquote>
                    <p class="mt-3">Consent is not a condition of purchasing any goods or services.</p>
                </section>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">2. Message frequency</h2>
                    <p class="mt-3"><strong>Message frequency varies</strong> depending on your inquiry and your interaction with us.</p>
                </section>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">3. Message &amp; data rates</h2>
                    <p class="mt-3"><strong>Message and data rates may apply</strong>, depending on your mobile carrier and plan. {{ company.name }} is not responsible for any charges from your carrier.</p>
                </section>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">4. Opting out — reply STOP</h2>
                    <p class="mt-3">You can cancel the SMS service at any time by texting <strong>STOP</strong> to any message you receive. After you send STOP, we will send a confirmation message and then stop sending SMS messages. To opt in again, submit the consent form once more.</p>
                </section>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">5. Help — reply HELP</h2>
                    <p class="mt-3">If you experience any issues or need assistance, reply <strong>HELP</strong> to any message, or email us at <a :href="`mailto:${company.email}`" class="link">{{ company.email }}</a>.</p>
                </section>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">6. Carriers &amp; privacy</h2>
                    <p class="mt-3">Carriers are not liable for delayed or undelivered messages. We handle your information in accordance with our <Link href="/privacy" class="link">Privacy Policy</Link>, and we do not sell your information to third parties.</p>
                </section>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">7. Company contact information</h2>
                    <ul class="mt-3 space-y-1.5">
                        <li><strong>Company:</strong> {{ company.legalName }}</li>
                        <li><strong>Email:</strong> <a :href="`mailto:${company.email}`" class="link">{{ company.email }}</a></li>
                        <li><strong>Website:</strong> <a :href="company.website" class="link">{{ company.website }}</a></li>
                        <li><strong>Business hours:</strong> {{ company.hours }}</li>
                    </ul>
                </section>
            </div>
        </main>

        <footer class="border-t border-stone-200 bg-white">
            <div class="mx-auto flex max-w-3xl flex-col items-center justify-between gap-3 px-5 py-8 text-stone-500 sm:flex-row sm:px-8">
                <p class="text-sm">© {{ year }} {{ company.legalName }}.</p>
                <nav class="flex gap-7 text-sm">
                    <Link href="/privacy" class="hover:text-stone-900">Privacy Policy</Link>
                    <Link href="/terms" class="hover:text-stone-900">Terms &amp; Conditions</Link>
                    <Link href="/contact" class="hover:text-stone-900">Contact Us</Link>
                </nav>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.serif { font-family: 'Fraunces', Georgia, 'Times New Roman', serif; font-optical-sizing: auto; }
.eyebrow { font-family: 'Geist Mono', ui-monospace, SFMono-Regular, monospace; text-transform: uppercase; letter-spacing: 0.2em; font-weight: 500; }
.link { color: #1c1917; text-decoration: underline; text-decoration-color: #d6d3d1; text-underline-offset: 2px; }
.link:hover { text-decoration-color: #1c1917; }
</style>
