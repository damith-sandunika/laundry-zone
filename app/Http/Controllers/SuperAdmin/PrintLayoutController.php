<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Central\TenantPrintLayout;
use App\Services\Tenancy\TenantManager;
use Illuminate\Http\Request;

class PrintLayoutController extends Controller
{
    /**
     * Display layout customizer dashboard.
     */
    public function index(Request $request)
    {
        TenantManager::resetToLandlord();

        $tenants = Tenant::where('status', 'active')->orderBy('name')->get();
        $selectedTenantId = $request->query('tenant_id', $tenants->first()?->id);
        $currentType = $request->query('type', 'bill'); // bill, receipt, invoice

        $selectedTenant = $selectedTenantId ? Tenant::find($selectedTenantId) : $tenants->first();
        
        $layout = TenantPrintLayout::getForTenant($selectedTenant?->id, $currentType);

        return view('super_admin.layouts.index', compact(
            'tenants',
            'selectedTenant',
            'currentType',
            'layout'
        ));
    }

    /**
     * Save custom layout for a company.
     */
    public function update(Request $request)
    {
        TenantManager::resetToLandlord();

        $request->validate([
            'tenant_id' => 'required|exists:landlord.tenants,id',
            'type' => 'required|in:bill,receipt,invoice',
            'font_family' => 'required|string',
            'font_size' => 'required|string',
            'primary_color' => 'required|string',
            'accent_color' => 'required|string',
            'header_title' => 'nullable|string|max:100',
            'header_subtitle' => 'nullable|string|max:150',
            'terms_and_conditions' => 'nullable|string',
            'footer_text' => 'nullable|string|max:200',
        ]);

        $layout = TenantPrintLayout::firstOrNew([
            'tenant_id' => $request->tenant_id,
            'type' => $request->type,
        ]);

        $layout->fill([
            'name' => $request->input('name', ucfirst($request->type) . ' Layout'),
            'font_family' => $request->font_family,
            'font_size' => $request->font_size,
            'primary_color' => $request->primary_color,
            'accent_color' => $request->accent_color,
            'show_logo' => $request->boolean('show_logo'),
            'show_header' => $request->boolean('show_header'),
            'show_branch_info' => $request->boolean('show_branch_info'),
            'show_customer_info' => $request->boolean('show_customer_info'),
            'show_qr_code' => $request->boolean('show_qr_code'),
            'show_barcode' => $request->boolean('show_barcode'),
            'show_notes' => $request->boolean('show_notes'),
            'show_footer' => $request->boolean('show_footer'),
            'header_title' => $request->header_title,
            'header_subtitle' => $request->header_subtitle,
            'terms_and_conditions' => $request->terms_and_conditions,
            'footer_text' => $request->footer_text,
        ]);

        $layout->save();

        return redirect()->route('super_admin.layouts.index', [
            'tenant_id' => $request->tenant_id,
            'type' => $request->type,
        ])->with('success', 'Layout design updated successfully!');
    }

    /**
     * Reset to default template.
     */
    public function reset(Request $request)
    {
        TenantManager::resetToLandlord();

        $request->validate([
            'tenant_id' => 'required|exists:landlord.tenants,id',
            'type' => 'required|in:bill,receipt,invoice',
        ]);

        $layout = TenantPrintLayout::where('tenant_id', $request->tenant_id)
            ->where('type', $request->type)
            ->first();

        if ($layout) {
            $layout->update(TenantPrintLayout::defaultAttributes($request->type));
        }

        return back()->with('success', 'Layout reset to standard factory default.');
    }
}

