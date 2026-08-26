<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCompanySettingRequest;
use App\Models\CompanySetting;
use App\Services\Finance\InvoiceNumberService;
use Illuminate\Support\Facades\Storage;

class CompanySettingController extends Controller
{
    public function __construct(private readonly InvoiceNumberService $invoiceNumbers)
    {
        $this->middleware('role:Admin');
    }

    public function edit()
    {
        $companySetting = CompanySetting::current();
        $nextInvoiceId = $this->invoiceNumbers->nextSequentialId();
        $nextInvoiceNumberPreview = $this->invoiceNumbers->format($companySetting->invoice_number_pattern, $nextInvoiceId);

        return view('admin.company-settings.edit', compact('companySetting', 'nextInvoiceId', 'nextInvoiceNumberPreview'));
    }

    public function update(UpdateCompanySettingRequest $request)
    {
        $companySetting = CompanySetting::current();
        $data = $request->validated();
        unset($data['logo_file'], $data['remove_logo']);

        if ($request->boolean('remove_logo') && $companySetting->logo_path) {
            Storage::disk('public')->delete($companySetting->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo_file')) {
            if ($companySetting->logo_path) {
                Storage::disk('public')->delete($companySetting->logo_path);
            }
            $data['logo_path'] = $request->file('logo_file')->store('company', 'public');
        }

        $companySetting->update($data);

        return redirect()->route('admin.company-settings.edit')
            ->with('success', 'Company settings updated successfully.');
    }
}
