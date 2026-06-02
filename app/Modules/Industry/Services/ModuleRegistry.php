<?php

declare(strict_types=1);

namespace App\Modules\Industry\Services;

/**
 * Catalogue of installable industry modules. Each module ships one manifest-
 * defined entity (a "custom object"): the generic record controller + UI build
 * list/create/validation from this manifest, so a module is real and functional
 * without bespoke code. Field types: text, textarea, number, money, select, date.
 */
final class ModuleRegistry
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function modules(): array
    {
        return [
            [
                'key' => 'real_estate',
                'name' => 'Real Estate',
                'description' => 'Track property listings and link them to buyers and sellers.',
                'icon' => 'home',
                'entity' => [
                    'key' => 'property', 'label' => 'Properties', 'singular' => 'Property',
                    'title_field' => 'address', 'status_field' => 'status', 'links_contact' => true, 'contact_label' => 'Owner / buyer',
                    'fields' => [
                        ['key' => 'address', 'label' => 'Address', 'type' => 'text', 'required' => true],
                        ['key' => 'price', 'label' => 'Price', 'type' => 'money', 'required' => true],
                        ['key' => 'bedrooms', 'label' => 'Bedrooms', 'type' => 'number'],
                        ['key' => 'type', 'label' => 'Type', 'type' => 'select', 'options' => ['House', 'Apartment', 'Land', 'Commercial']],
                        ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['For sale', 'Under offer', 'Sold']],
                    ],
                ],
            ],
            [
                'key' => 'recruitment',
                'name' => 'Recruitment',
                'description' => 'Manage candidates through your hiring pipeline.',
                'icon' => 'people',
                'entity' => [
                    'key' => 'candidate', 'label' => 'Candidates', 'singular' => 'Candidate',
                    'title_field' => 'name', 'status_field' => 'stage', 'links_contact' => true, 'contact_label' => 'Contact',
                    'fields' => [
                        ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                        ['key' => 'role', 'label' => 'Role applied for', 'type' => 'text', 'required' => true],
                        ['key' => 'stage', 'label' => 'Stage', 'type' => 'select', 'required' => true, 'options' => ['Applied', 'Screening', 'Interview', 'Offer', 'Hired', 'Rejected']],
                        ['key' => 'source', 'label' => 'Source', 'type' => 'text'],
                    ],
                ],
            ],
            [
                'key' => 'education',
                'name' => 'Education',
                'description' => 'Keep a roster of students and their enrolment status.',
                'icon' => 'edu',
                'entity' => [
                    'key' => 'student', 'label' => 'Students', 'singular' => 'Student',
                    'title_field' => 'name', 'status_field' => 'status', 'links_contact' => true, 'contact_label' => 'Guardian contact',
                    'fields' => [
                        ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                        ['key' => 'program', 'label' => 'Program', 'type' => 'text', 'required' => true],
                        ['key' => 'year', 'label' => 'Year', 'type' => 'select', 'options' => ['1st', '2nd', '3rd', '4th']],
                        ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Enrolled', 'Graduated', 'Withdrawn']],
                    ],
                ],
            ],
            [
                'key' => 'healthcare',
                'name' => 'Healthcare',
                'description' => 'A patient register. Enable workspace audit logging for HIPAA-style trails.',
                'icon' => 'health',
                'hipaa' => true,
                'entity' => [
                    'key' => 'patient', 'label' => 'Patients', 'singular' => 'Patient',
                    'title_field' => 'name', 'status_field' => 'status', 'links_contact' => true, 'contact_label' => 'Linked contact',
                    'fields' => [
                        ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                        ['key' => 'dob', 'label' => 'Date of birth', 'type' => 'date'],
                        ['key' => 'condition', 'label' => 'Primary condition', 'type' => 'text'],
                        ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'required' => true, 'options' => ['Active', 'Discharged']],
                    ],
                ],
            ],
        ];
    }

    /** @return array<int, string> */
    public static function keys(): array
    {
        return array_column(self::modules(), 'key');
    }

    /** @return array<string, mixed>|null */
    public static function find(string $moduleKey): ?array
    {
        foreach (self::modules() as $module) {
            if ($module['key'] === $moduleKey) {
                return $module;
            }
        }

        return null;
    }

    /**
     * The single entity manifest for a module, or null if the module/entity is
     * unknown.
     *
     * @return array<string, mixed>|null
     */
    public static function entity(string $moduleKey, string $entityKey): ?array
    {
        $module = self::find($moduleKey);
        if ($module === null || $module['entity']['key'] !== $entityKey) {
            return null;
        }

        return $module['entity'];
    }
}
