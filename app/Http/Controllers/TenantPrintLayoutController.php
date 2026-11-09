<?php

namespace App\Http\Controllers;

use App\Models\Central\Tenant;
use App\Models\Central\TenantPrintLayout;
use App\Services\Tenancy\TenantManager;
use Illuminate\Http\Request;

class TenantPrintLayoutController extends Controller
{
    /**
     * Display layout customizer dashboard for the current tenant company.
     */
    public function index(Request $request)
    {
        $type = $request->query('type', 'bill');
        return $this->renderLayoutPage($type);
    }

    public function billPage()
    {
        return $this->renderLayoutPage('bill');
    }

    public function receiptPage()
    {
        return $this->renderLayoutPage('receipt');
    }

    public function invoicePage()
    {
        return $this->renderLayoutPage('invoice');
    }

    private function renderLayoutPage(string $currentType)
    {
        $tenant = TenantManager::getTenant();
        $layout = TenantPrintLayout::getForTenant($tenant?->id, $currentType);

        // Ensure default custom_fields are merged if empty or missing
        $defaultAttrs = TenantPrintLayout::defaultAttributes($currentType);
        $mergedCustomFields = array_merge(
            $defaultAttrs['custom_fields'] ?? [],
            $layout->custom_fields ?? []
        );
        $layout->custom_fields = $mergedCustomFields;

        if (empty($layout->header_title)) {
            $layout->header_title = $defaultAttrs['header_title'];
        }
        if (empty($layout->header_subtitle)) {
            $layout->header_subtitle = $defaultAttrs['header_subtitle'];
        }
        if (empty($layout->terms_and_conditions)) {
            $layout->terms_and_conditions = $defaultAttrs['terms_and_conditions'];
        }
        if (empty($layout->footer_text)) {
            $layout->footer_text = $defaultAttrs['footer_text'];
        }

        $viewMap = [
            'bill' => 'layouts.documents.bill',
            'receipt' => 'layouts.documents.receipt',
            'invoice' => 'layouts.documents.invoice',
        ];

        $view = $viewMap[$currentType] ?? 'layouts.documents.bill';

        return view($view, compact('tenant', 'currentType', 'layout'));
    }

    /**
     * Save custom layout for the tenant company.
     */
    public function update(Request $request)
    {
        $tenant = TenantManager::getTenant();

        if (!$tenant) {
            return back()->with('error', 'Tenant context not identified.');
        }

        $request->validate([
            'type' => 'required|in:bill,receipt,invoice',
            'font_family' => 'nullable|string',
            'font_size' => 'nullable|string',
            'primary_color' => 'nullable|string',
            'accent_color' => 'nullable|string',
            'header_title' => 'nullable|string|max:100',
            'header_subtitle' => 'nullable|string|max:150',
            'terms_and_conditions' => 'nullable|string',
            'footer_text' => 'nullable|string|max:200',
            'custom_fields' => 'nullable|array',
            'process_items_json' => 'nullable|string',
        ]);

        $defaultAttrs = TenantPrintLayout::defaultAttributes($request->type);

        $layout = TenantPrintLayout::firstOrNew([
            'tenant_id' => $tenant->id,
            'type' => $request->type,
        ]);

        // Deep merge custom fields with defaults and existing custom fields
        $existingCustom = is_array($layout->custom_fields) ? $layout->custom_fields : [];
        $incomingCustom = $request->input('custom_fields', []);

        // Handle process_items if submitted as JSON or array
        if ($request->filled('process_items_json')) {
            $decoded = json_decode($request->input('process_items_json'), true);
            if (is_array($decoded)) {
                $incomingCustom['process_items'] = array_values($decoded);
            }
        } elseif (isset($incomingCustom['process_items']) && is_array($incomingCustom['process_items'])) {
            $incomingCustom['process_items'] = array_values($incomingCustom['process_items']);
        }

        // Handle custom_extra_texts if submitted as JSON in bill custom_fields
        if (!empty($incomingCustom['custom_extra_texts_json'])) {
            $decodedExtras = json_decode($incomingCustom['custom_extra_texts_json'], true);
            if (is_array($decodedExtras)) {
                $incomingCustom['custom_extra_texts'] = array_values($decodedExtras);
            }
            unset($incomingCustom['custom_extra_texts_json']);
        }

        $mergedCustom = array_merge(
            $defaultAttrs['custom_fields'] ?? [],
            $existingCustom,
            $incomingCustom
        );

        $layout->name = ucfirst($request->type) . ' Layout';
        $layout->font_family = $request->input('font_family') ?: ($layout->font_family ?: $defaultAttrs['font_family']);
        $layout->font_size = $request->input('font_size') ?: ($layout->font_size ?: $defaultAttrs['font_size']);
        $layout->primary_color = $request->input('primary_color') ?: ($layout->primary_color ?: $defaultAttrs['primary_color']);
        $layout->accent_color = $request->input('accent_color') ?: ($layout->accent_color ?: $defaultAttrs['accent_color']);

        $layout->show_logo = $request->boolean('show_logo');
        $layout->show_header = $request->has('show_header') ? $request->boolean('show_header') : true;
        $layout->show_branch_info = $request->has('show_branch_info') ? $request->boolean('show_branch_info') : true;
        $layout->show_customer_info = $request->has('show_customer_info') ? $request->boolean('show_customer_info') : true;
        $layout->show_qr_code = $request->has('show_qr_code') ? $request->boolean('show_qr_code') : true;
        $layout->show_barcode = $request->has('show_barcode') ? $request->boolean('show_barcode') : false;
        $layout->show_notes = $request->has('show_notes') ? $request->boolean('show_notes') : true;
        $layout->show_footer = $request->has('show_footer') ? $request->boolean('show_footer') : true;

        $layout->header_title = $request->filled('header_title')
            ? $request->input('header_title')
            : ($layout->header_title ?: $defaultAttrs['header_title']);

        $layout->header_subtitle = $request->has('header_subtitle')
            ? $request->input('header_subtitle')
            : ($layout->header_subtitle ?: $defaultAttrs['header_subtitle']);

        $layout->terms_and_conditions = $request->has('terms_and_conditions')
            ? $request->input('terms_and_conditions')
            : ($layout->terms_and_conditions ?: $defaultAttrs['terms_and_conditions']);

        $layout->footer_text = $request->has('footer_text')
            ? $request->input('footer_text')
            : ($layout->footer_text ?: $defaultAttrs['footer_text']);

        $layout->custom_fields = $mergedCustom;

        $layout->save();

        $redirectMap = [
            'bill' => 'tenant.layouts.bill',
            'receipt' => 'tenant.layouts.receipt',
            'invoice' => 'tenant.layouts.invoice',
        ];

        $route = $redirectMap[$request->type] ?? 'tenant.layouts.bill';

        return redirect()->route($route)
            ->with('success', ucfirst($request->type) . ' layout updated successfully!');
    }

    /**
     * Reset to default template.
     */
    public function reset(Request $request)
    {
        $tenant = TenantManager::getTenant();

        $request->validate([
            'type' => 'required|in:bill,receipt,invoice',
        ]);

        if ($tenant) {
            $layout = TenantPrintLayout::where('tenant_id', $tenant->id)
                ->where('type', $request->type)
                ->first();

            if ($layout) {
                $layout->update(TenantPrintLayout::defaultAttributes($request->type));
            }
        }

        $redirectMap = [
            'bill' => 'tenant.layouts.bill',
            'receipt' => 'tenant.layouts.receipt',
            'invoice' => 'tenant.layouts.invoice',
        ];

        $route = $redirectMap[$request->type] ?? 'tenant.layouts.bill';

        return redirect()->route($route)
            ->with('success', 'Layout reset to default template.');
    }
}
