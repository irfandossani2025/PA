<?php

namespace App\Services;

use App\Models\ServiceOffering;
use Illuminate\Support\Collection;

class BusinessKnowledgeBase
{
    /**
     * @return Collection<int, ServiceOffering>
     */
    public function activeOfferings(): Collection
    {
        return ServiceOffering::query()
            ->active()
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }

    public function installDefaultItOfferings(): int
    {
        $offerings = [
            [
                'slug' => 'ai-voice-calling-agents',
                'name' => 'AI Voice Calling Agents',
                'category' => 'AI automation',
                'starting_price_omr' => 1000,
                'price_note' => 'OMR 1,000–2,500 setup, plus usage. Scope and integrations determine the final proposal.',
                'summary' => 'Arabic and English AI calling agents for 24/7 bookings, confirmations, payment reminders, and lead follow-up.',
                'capabilities' => ['Arabic and English conversations', 'Appointment and booking workflows', 'Payment reminders', 'Lead follow-up', '24/7 coverage'],
                'sales_playbook' => [
                    'ideal_for' => ['Businesses losing leads after hours', 'Teams handling repetitive outbound calls', 'Appointments, confirmations, and payment follow-up'],
                    'discovery_questions' => ['Which calls take the most staff time?', 'Which languages must callers support?', 'Which booking, CRM, or payment system should it connect to?', 'What is the expected monthly call volume?'],
                    'qualification_note' => 'Describe time and lead-recovery outcomes as estimates, never guarantees. Confirm telephony, consent, and integration requirements before quoting.',
                ],
                'position' => 10,
            ],
            [
                'slug' => 'multi-channel-ai-chatbots',
                'name' => 'Multi-Channel AI Chatbots',
                'category' => 'AI automation',
                'starting_price_omr' => 400,
                'price_note' => 'Website chatbot from OMR 400; WhatsApp chatbot commonly OMR 1,000–2,000 depending on workflow and integrations.',
                'summary' => 'Website and WhatsApp assistants that answer FAQs, capture leads, qualify enquiries, and provide always-on first responses.',
                'capabilities' => ['Website chatbot', 'WhatsApp workflow chatbot', 'FAQ responses', 'Lead capture and qualification', 'Escalation to a human team'],
                'sales_playbook' => [
                    'ideal_for' => ['Businesses with repetitive customer questions', 'Sales teams needing faster first response', 'Companies that want after-hours lead capture'],
                    'discovery_questions' => ['Where do enquiries arrive today?', 'What questions repeat most often?', 'What qualifies a lead?', 'Who should receive escalated enquiries?'],
                    'qualification_note' => 'Do not promise a fixed query-deflection or conversion percentage. Confirm WhatsApp approval and the required integrations.',
                ],
                'position' => 20,
            ],
            [
                'slug' => 'website-and-portal-development',
                'name' => 'Website & Portal Development',
                'category' => 'Engineering',
                'starting_price_omr' => 400,
                'price_note' => 'From OMR 400. Final price depends on pages, integrations, content, and e-commerce or portal requirements.',
                'summary' => 'Custom responsive websites, client portals, e-commerce experiences, and UI/UX-led digital platforms.',
                'capabilities' => ['Custom UI/UX', 'Responsive websites', 'E-commerce', 'Client and business portals', 'Content and conversion-focused structure'],
                'sales_playbook' => [
                    'ideal_for' => ['Businesses needing a new digital presence', 'Teams that need a customer portal', 'Companies selling online'],
                    'discovery_questions' => ['What should visitors be able to achieve?', 'How many key pages or user roles are needed?', 'Do you need payments, e-commerce, or a portal?', 'Do you have existing content and branding?'],
                    'qualification_note' => 'Establish the required user journeys and integrations before using a starting price in a proposal.',
                ],
                'position' => 30,
            ],
            [
                'slug' => 'mobile-app-development',
                'name' => 'Mobile App Development',
                'category' => 'Engineering',
                'starting_price_omr' => 1200,
                'price_note' => 'From OMR 1,200. Scope depends on platform, roles, backend, integrations, and app-store release needs.',
                'summary' => 'Native iOS and Android mobile applications with push notifications, backend connectivity, and app-store delivery planning.',
                'capabilities' => ['iOS and Android applications', 'Push notifications', 'Backend and API connectivity', 'User authentication and roles', 'App-store release support'],
                'sales_playbook' => [
                    'ideal_for' => ['Businesses with a mobile-first customer workflow', 'Companies needing field operations or customer apps', 'Existing web products extending to mobile'],
                    'discovery_questions' => ['Who will use the app and what is the primary job to complete?', 'Is there an existing API or backend?', 'Which platforms are required?', 'Do users need notifications, payments, or location features?'],
                    'qualification_note' => 'Never promise app-store approval or a launch date until requirements, accounts, compliance, and review constraints are known.',
                ],
                'position' => 40,
            ],
            [
                'slug' => 'custom-software',
                'name' => 'Custom Software',
                'category' => 'Engineering',
                'starting_price_omr' => null,
                'price_note' => 'Project-based pricing after discovery and solution design.',
                'summary' => 'Custom ERPs, CRMs, APIs, cloud architecture, and business systems designed around a company’s workflow.',
                'capabilities' => ['ERP and CRM solutions', 'Business process automation', 'API integrations', 'Cloud architecture', 'Custom internal systems'],
                'sales_playbook' => [
                    'ideal_for' => ['Businesses working across disconnected spreadsheets or tools', 'Teams with a repeatable operational workflow to digitize', 'Companies needing system integration'],
                    'discovery_questions' => ['Which process is currently manual or duplicated?', 'Which systems must exchange data?', 'Which roles need access?', 'What reporting or approval workflow is needed?'],
                    'qualification_note' => 'Run a paid or scoped discovery before offering a firm price or delivery timeline.',
                ],
                'position' => 50,
            ],
            [
                'slug' => 'comprehensive-digital-marketing',
                'name' => 'Comprehensive Digital Marketing',
                'category' => 'Growth',
                'starting_price_omr' => 200,
                'price_note' => 'From OMR 200 per month. Channels, creative volume, media spend, and reporting scope are confirmed separately.',
                'summary' => 'Ongoing digital marketing across AI-assisted social content, advertising, SEO, and performance analytics.',
                'capabilities' => ['Social content planning', 'Paid advertising support', 'SEO support', 'Performance analytics', 'Campaign reporting'],
                'sales_playbook' => [
                    'ideal_for' => ['Businesses needing a consistent pipeline of marketing activity', 'Companies with unclear campaign performance', 'Teams needing social, ads, and SEO coordination'],
                    'discovery_questions' => ['What is the business goal: leads, sales, awareness, or retention?', 'Which channels are active today?', 'What monthly media budget is available?', 'How are leads tracked after they arrive?'],
                    'qualification_note' => 'Separate service fees from ad spend and do not guarantee lead volume, ranking, or revenue.',
                ],
                'position' => 60,
            ],
            [
                'slug' => 'google-maps-optimization',
                'name' => 'Google Maps Optimization',
                'category' => 'Growth',
                'starting_price_omr' => 100,
                'price_note' => 'From OMR 100 per month. Scope includes profile, local keywords, reviews, and reporting as agreed.',
                'summary' => 'Google Business Profile and local-search optimization focused on profile quality, local keywords, review workflows, and reporting.',
                'capabilities' => ['Google Business Profile improvement', 'Local keyword optimization', 'Review-response workflow', 'Local visibility reporting'],
                'sales_playbook' => [
                    'ideal_for' => ['Local service businesses', 'Businesses with a physical location', 'Companies receiving map or local-search enquiries'],
                    'discovery_questions' => ['Which locations should be optimized?', 'Is the Google Business Profile already claimed?', 'What services and local areas are most valuable?', 'Who can request and respond to reviews?'],
                    'qualification_note' => 'Do not promise a specific map ranking. Follow Google policies and use genuine reviews only.',
                ],
                'position' => 70,
            ],
        ];

        foreach ($offerings as $offering) {
            ServiceOffering::query()->updateOrCreate(
                ['slug' => $offering['slug']],
                array_merge($offering, [
                    'business_area' => 'it',
                    'is_active' => true,
                ]),
            );
        }

        return count($offerings) + $this->installDefaultCorporateGiftSources();
    }

    private function installDefaultCorporateGiftSources(): int
    {
        $sources = [
            [
                'slug' => 'corporate-gifts-luxury-trading',
                'name' => 'Luxury Trading Corporate Gifts',
                'category' => 'Approved supplier source',
                'starting_price_omr' => null,
                'price_note' => 'Request current supplier quotation and availability before preparing a customer proposal.',
                'summary' => 'Approved corporate-gift source for branded notebooks, organizers, drinkware, bags, technology gifts, pens, flash drives, gift sets, eco-friendly items, and customization.',
                'capabilities' => ['Notebooks and organizers', 'Drinkware', 'Bags', 'Technology gifts and charging accessories', 'Pens and flash drives', 'Gift sets and boxes', 'Eco-friendly items', 'Custom branding'],
                'sales_playbook' => [
                    'ideal_for' => ['Premium corporate gifting', 'Branded technology and office gifts', 'Eco-friendly gift programs', 'Executive and event gift sets'],
                    'discovery_questions' => ['What is the recipient profile and occasion?', 'How many units are required?', 'What is the target budget per recipient in OMR?', 'Which branding method or logo files are available?', 'What is the required delivery date and location?'],
                    'qualification_note' => 'Check live stock, customization feasibility, lead time, and supplier cost privately. Never promise availability or reveal supplier cost in a customer quotation.',
                ],
                'position' => 110,
            ],
            [
                'slug' => 'corporate-gifts-mtc',
                'name' => 'MTC Promotional Gifts',
                'category' => 'Approved supplier source',
                'starting_price_omr' => null,
                'price_note' => 'Request current supplier quotation and availability before preparing a customer proposal.',
                'summary' => 'Approved promotional-product source with technology gifts, drinkware, stationery, apparel, bags, awards, packaging, eco-friendly products, and print-related options.',
                'capabilities' => ['Technology gifts', 'Drinkware and bottles', 'Stationery and notebooks', 'Apparel and bags', 'Awards, plaques, and trophies', 'Packaging', 'Eco-friendly gifts', 'ID and event products'],
                'sales_playbook' => [
                    'ideal_for' => ['Large promotional campaigns', 'Employee and event giveaways', 'Awards and recognition', 'Branded merchandise programs'],
                    'discovery_questions' => ['Is this for an event, employee program, client gift, or campaign?', 'What quantities, target delivery date, and destination apply?', 'Are there branding colors or printing requirements?', 'Does the customer need gift boxes or packaging?'],
                    'qualification_note' => 'Check live stock, customization feasibility, lead time, and supplier cost privately. Never promise availability or reveal supplier cost in a customer quotation.',
                ],
                'position' => 120,
            ],
            [
                'slug' => 'corporate-gifts-jasani',
                'name' => 'Jasani Corporate Gifts',
                'category' => 'Approved supplier source',
                'starting_price_omr' => null,
                'price_note' => 'Request current supplier quotation and availability before preparing a customer proposal.',
                'summary' => 'Approved UAE corporate-gift supplier source for branded promotional gifts and custom corporate-gifting requirements.',
                'capabilities' => ['Corporate gifts', 'Promotional giveaways', 'Custom-branded merchandise', 'Event and employee gifting'],
                'sales_playbook' => [
                    'ideal_for' => ['Custom corporate-gift briefs', 'Client and employee appreciation', 'Promotional product sourcing'],
                    'discovery_questions' => ['What is the occasion and recipient profile?', 'What quantity and delivery deadline apply?', 'What brand assets and customization are required?', 'What is the OMR budget per unit or total budget?'],
                    'qualification_note' => 'Confirm the precise product range, live stock, customization feasibility, lead time, and private supplier cost before proposing options.',
                ],
                'position' => 130,
            ],
            [
                'slug' => 'corporate-gifts-hakplus',
                'name' => 'Hakplus Corporate Gifts',
                'category' => 'Approved supplier source',
                'starting_price_omr' => null,
                'price_note' => 'Request current supplier quotation and availability before preparing a customer proposal.',
                'summary' => 'Approved UAE supplier source for bulk customized corporate gifts and branded promotional products.',
                'capabilities' => ['Bulk corporate gifts', 'Custom-branded products', 'Promotional giveaways', 'Business and event gifting'],
                'sales_playbook' => [
                    'ideal_for' => ['Bulk customized orders', 'Promotional campaigns', 'Corporate events and client gifts'],
                    'discovery_questions' => ['What product type fits the recipient and occasion?', 'What is the required quantity?', 'What branding, packaging, and delivery timeline are needed?', 'What is the target OMR budget?'],
                    'qualification_note' => 'Confirm product selection, live stock, customization feasibility, lead time, and private supplier cost before a customer proposal.',
                ],
                'position' => 140,
            ],
        ];

        foreach ($sources as $source) {
            ServiceOffering::query()->updateOrCreate(
                ['slug' => $source['slug']],
                array_merge($source, [
                    'business_area' => 'corporate_gifts',
                    'is_active' => true,
                ]),
            );
        }

        return count($sources);
    }

    public function assistantContext(): string
    {
        $offerings = $this->activeOfferings();

        if ($offerings->isEmpty()) {
            return 'No internal business catalogue has been installed yet. Do not invent offerings, product sources, prices, or delivery commitments.';
        }

        return $offerings->map(function (ServiceOffering $offering): string {
            $playbook = $offering->sales_playbook;
            $price = $offering->starting_price_omr === null
                ? $offering->price_note
                : 'Starting price: OMR '.$offering->starting_price_omr.'. '.$offering->price_note;

            return implode("\n", [
                "BUSINESS AREA: {$offering->business_area}",
                "OFFERING OR SOURCE: {$offering->name} ({$offering->category})",
                "SUMMARY: {$offering->summary}",
                "PRICE: {$price}",
                'CAPABILITIES: '.implode('; ', $offering->capabilities),
                'BEST FIT: '.implode('; ', $playbook['ideal_for']),
                'DISCOVERY QUESTIONS: '.implode(' | ', $playbook['discovery_questions']),
                "QUALIFICATION: {$playbook['qualification_note']}",
            ]);
        })->implode("\n\n");
    }
}
