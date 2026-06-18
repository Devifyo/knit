<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineOptions({ layout: null });
defineProps({ company: Object, consentText: String });
const year = new Date().getFullYear();
</script>

<template>
    <Head title="Privacy Policy — Knit" />

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
            <h1 class="serif mt-3 text-4xl font-medium tracking-[-0.02em] sm:text-5xl">Privacy Policy</h1>
            <p class="mt-3 text-sm text-stone-500">Last updated: June 2026</p>

            <div class="prose-legal mt-10 space-y-9 text-[15px] leading-relaxed text-stone-700">
                <p>This Privacy Policy explains how {{ company.legalName }} ("{{ company.name }}", "we", "us") collects, uses and protects your personal information when you contact us or request a consultation through {{ company.website }}.</p>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">1. Information we collect</h2>
                    <p class="mt-3">When you submit our consultation form, we collect the information you provide:</p>
                    <ul class="mt-3 list-disc space-y-1.5 pl-6 marker:text-stone-400">
                        <li><strong>Name</strong> — to identify and address you.</li>
                        <li><strong>Email address</strong> — to respond to your inquiry and send project-related information.</li>
                        <li><strong>Phone number</strong> — to contact you about your inquiry and, if you opt in, to send SMS communications.</li>
                        <li>Optional details you choose to share, such as your company name and project requirements.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">2. SMS communications</h2>
                    <p class="mt-3">If you check the SMS consent box on our form, you expressly opt in to receive text messages from {{ company.name }} regarding your inquiry, project updates, service information, and promotional offers. The consent you agree to reads:</p>
                    <blockquote class="mt-4 border-l-2 border-stone-900/20 pl-4 text-sm italic text-stone-500">{{ consentText }}</blockquote>
                    <p class="mt-3">Message frequency varies. Message and data rates may apply. Providing SMS consent is not a condition of purchasing any product or service. We do not send text messages unless you have opted in.</p>
                </section>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">3. How to opt out</h2>
                    <p class="mt-3">You can opt out of SMS messages at any time by replying <strong>STOP</strong> to any message you receive from us. After you reply STOP, we will stop sending you SMS messages. For help, reply <strong>HELP</strong> or contact us at <a :href="`mailto:${company.email}`" class="link">{{ company.email }}</a>.</p>
                </section>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">4. How we use your information</h2>
                    <p class="mt-3">We use your information solely to respond to your inquiry, provide consultations and proposals, deliver the services you request, and — with your consent — send the SMS communications described above.</p>
                </section>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">5. We do not sell your information</h2>
                    <p class="mt-3"><strong>We do not sell, rent or trade your personal information to third parties.</strong> Your phone number and SMS consent are never shared with third parties for their own marketing purposes. We share information only with service providers who help us operate (for example, our messaging or email provider), and only as needed to provide the service.</p>
                </section>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">6. Data retention &amp; security</h2>
                    <p class="mt-3">We retain your information for as long as necessary to respond to your inquiry and meet legal or compliance obligations, and we take reasonable measures to protect it.</p>
                </section>

                <section>
                    <h2 class="serif text-2xl font-medium text-stone-900">7. Contact us</h2>
                    <p class="mt-3">Questions about this policy? Contact us at <a :href="`mailto:${company.email}`" class="link">{{ company.email }}</a> or visit <a :href="company.website" class="link">{{ company.website }}</a>. Business hours: {{ company.hours }}.</p>
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
