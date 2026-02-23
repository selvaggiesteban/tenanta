<?php

namespace App\Services;

use App\Models\Tenant;

class BrandingService
{
    /**
     * Get branding configuration for a tenant.
     * Falls back to defaults for any missing values.
     */
    public function resolve(?Tenant $tenant = null): array
    {
        $defaults = config('branding.defaults');

        if (!$tenant) {
            $tenant = app('current_tenant');
        }

        if (!$tenant) {
            return $this->formatBranding($defaults);
        }

        return $this->formatBranding([
            // Logos
            'logo_light' => $tenant->logo_light ?? $tenant->logo_url ?? $defaults['logo_light'],
            'logo_dark' => $tenant->logo_dark ?? $tenant->logo_url ?? $defaults['logo_dark'],
            'favicon' => $tenant->favicon ?? $defaults['favicon'],

            // Colors
            'primary_color' => $tenant->primary_color ?? $defaults['primary_color'],
            'secondary_color' => $tenant->secondary_color ?? $defaults['secondary_color'],

            // Contact
            'contact_email' => $tenant->contact_email ?? $defaults['contact_email'],
            'contact_phone' => $tenant->contact_phone ?? $defaults['contact_phone'],
            'contact_address' => $tenant->contact_address ?? $defaults['contact_address'],

            // Social
            'social_links' => $this->mergeSocialLinks(
                $tenant->social_links ?? [],
                $defaults['social_links']
            ),

            // SEO
            'meta_title' => $tenant->meta_title ?? $tenant->name ?? $defaults['meta_title'],
            'meta_description' => $tenant->meta_description ?? $defaults['meta_description'],
            'meta_keywords' => $tenant->meta_keywords ?? $defaults['meta_keywords'],

            // Regional
            'locale' => $tenant->locale ?? $defaults['locale'],
            'timezone' => $tenant->timezone ?? $defaults['timezone'],
            'currency' => $tenant->currency ?? $defaults['currency'],
            'date_format' => $tenant->date_format ?? $defaults['date_format'],

            // Tenant info
            'tenant_name' => $tenant->name,
            'tenant_slug' => $tenant->slug,
        ]);
    }

    /**
     * Get branding for public pages (by tenant slug).
     */
    public function resolveBySlug(string $slug): ?array
    {
        $tenant = Tenant::where('slug', $slug)->first();

        if (!$tenant) {
            return null;
        }

        return $this->resolve($tenant);
    }

    /**
     * Format branding data with additional computed values.
     */
    protected function formatBranding(array $branding): array
    {
        // Add currency symbol
        $currency = $branding['currency'] ?? 'ARS';
        $branding['currency_symbol'] = config("branding.currency_symbols.{$currency}", '$');

        // Add locale info
        $locale = $branding['locale'] ?? 'es_AR';
        $localeInfo = config("branding.locales.{$locale}", []);
        $branding['locale_name'] = $localeInfo['name'] ?? $locale;
        $branding['locale_flag'] = $localeInfo['flag'] ?? '';

        return $branding;
    }

    /**
     * Merge social links with defaults.
     */
    protected function mergeSocialLinks(array $tenantLinks, array $defaultLinks): array
    {
        if (is_string($tenantLinks)) {
            $tenantLinks = json_decode($tenantLinks, true) ?? [];
        }

        return array_merge($defaultLinks, array_filter($tenantLinks));
    }

    /**
     * Get available locales for selection.
     */
    public function getAvailableLocales(): array
    {
        return config('branding.locales', []);
    }

    /**
     * Get available timezones for selection.
     */
    public function getAvailableTimezones(): array
    {
        return config('branding.timezones', []);
    }

    /**
     * Get available currencies for selection.
     */
    public function getAvailableCurrencies(): array
    {
        return config('branding.currency_symbols', []);
    }
}
