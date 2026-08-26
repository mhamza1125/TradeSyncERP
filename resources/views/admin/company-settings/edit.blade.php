@extends('index')

@section('title', 'Company Settings - TradeSyncERP')

@section('content')
<div class="nxl-content">
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title"><h5 class="m-b-10">Company Settings</h5></div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Company Settings</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <button type="submit" form="companySettingForm" class="btn btn-primary">
                        <i class="feather-save me-2"></i><span>Save Changes</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="main-content">
        @include('partials.flash-messages')

        <form id="companySettingForm" action="{{ route('admin.company-settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-xl-8">

                    {{-- General Information --}}
                    <div class="card">
                        <div class="card-header"><h5 class="card-title">General Information</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-8 mb-4">
                                    <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                    <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                                           value="{{ old('company_name', $companySetting->company_name) }}" required>
                                    @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Tagline</label>
                                    <input type="text" name="tagline" class="form-control @error('tagline') is-invalid @enderror"
                                           value="{{ old('tagline', $companySetting->tagline) }}" placeholder="Optional slogan">
                                    @error('tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Contact Information --}}
                    <div class="card mt-4">
                        <div class="card-header"><h5 class="card-title">Contact Information</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror"
                                           value="{{ old('phone', $companySetting->phone) }}">
                                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Fax Number</label>
                                    <input type="text" name="fax" class="form-control @error('fax') is-invalid @enderror"
                                           value="{{ old('fax', $companySetting->fax) }}">
                                    @error('fax')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                           value="{{ old('email', $companySetting->email) }}">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label">Website</label>
                                    <input type="text" name="website" class="form-control @error('website') is-invalid @enderror"
                                           value="{{ old('website', $companySetting->website) }}" placeholder="www.example.com">
                                    @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-12 mb-4">
                                    <label class="form-label">Full Address</label>
                                    <textarea name="address" rows="2" class="form-control @error('address') is-invalid @enderror">{{ old('address', $companySetting->address) }}</textarea>
                                    @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">City</label>
                                    <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                                           value="{{ old('city', $companySetting->city) }}">
                                    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Country</label>
                                    <input type="text" name="country" class="form-control @error('country') is-invalid @enderror"
                                           value="{{ old('country', $companySetting->country) }}">
                                    @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" name="postal_code" class="form-control @error('postal_code') is-invalid @enderror"
                                           value="{{ old('postal_code', $companySetting->postal_code) }}">
                                    @error('postal_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tax & Registration --}}
                    <div class="card mt-4">
                        <div class="card-header"><h5 class="card-title">Tax & Registration</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">CNIC / Registration No.</label>
                                    <input type="text" name="registration_number" class="form-control @error('registration_number') is-invalid @enderror"
                                           value="{{ old('registration_number', $companySetting->registration_number) }}">
                                    @error('registration_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">NTN Number</label>
                                    <input type="text" name="ntn_number" class="form-control @error('ntn_number') is-invalid @enderror"
                                           value="{{ old('ntn_number', $companySetting->ntn_number) }}">
                                    @error('ntn_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">STRN Number</label>
                                    <input type="text" name="strn_number" class="form-control @error('strn_number') is-invalid @enderror"
                                           value="{{ old('strn_number', $companySetting->strn_number) }}">
                                    @error('strn_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Management --}}
                    <div class="card mt-4">
                        <div class="card-header"><h5 class="card-title">Management</h5></div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">CEO / Managing Director</label>
                                    <input type="text" name="ceo_name" class="form-control @error('ceo_name') is-invalid @enderror"
                                           value="{{ old('ceo_name', $companySetting->ceo_name) }}">
                                    @error('ceo_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Contact Person Name</label>
                                    <input type="text" name="contact_person_name" class="form-control @error('contact_person_name') is-invalid @enderror"
                                           value="{{ old('contact_person_name', $companySetting->contact_person_name) }}">
                                    @error('contact_person_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Contact Person Phone</label>
                                    <input type="text" name="contact_person_phone" class="form-control @error('contact_person_phone') is-invalid @enderror"
                                           value="{{ old('contact_person_phone', $companySetting->contact_person_phone) }}">
                                    @error('contact_person_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-lg-4 mb-4">
                                    <label class="form-label">Contact Person Email</label>
                                    <input type="email" name="contact_person_email" class="form-control @error('contact_person_email') is-invalid @enderror"
                                           value="{{ old('contact_person_email', $companySetting->contact_person_email) }}">
                                    @error('contact_person_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Document Defaults --}}
                    <div class="card mt-4">
                        <div class="card-header"><h5 class="card-title">Document Defaults</h5></div>
                        <div class="card-body">
                            <label class="form-label">Default Terms & Conditions</label>
                            <textarea name="default_terms" rows="3" class="form-control @error('default_terms') is-invalid @enderror"
                                      placeholder="Optional footer note reused on invoices and other documents">{{ old('default_terms', $companySetting->default_terms) }}</textarea>
                            @error('default_terms')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                </div>

                <div class="col-xl-4">
                    {{-- Branding / Logo --}}
                    <div class="card" x-data="{ preview: null, removeLogo: false }">
                        <div class="card-header"><h5 class="card-title">Branding</h5></div>
                        <div class="card-body">
                            <label class="form-label">Company Logo</label>
                            <p class="text-muted fs-12 mb-2">Used in document/PDF headers wherever company info appears.</p>

                            <div class="mb-3">
                                <template x-if="preview">
                                    <img :src="preview" alt="Logo preview" class="img-thumbnail" style="max-height:140px;">
                                </template>
                                <template x-if="!preview && !removeLogo && '{{ $companySetting->logo_url }}'">
                                    <img src="{{ $companySetting->logo_url }}" alt="Current logo" class="img-thumbnail" style="max-height:140px;">
                                </template>
                                <template x-if="!preview && (removeLogo || !'{{ $companySetting->logo_url }}')">
                                    <div class="text-muted fs-12 fst-italic">No logo uploaded.</div>
                                </template>
                            </div>

                            <input type="file" name="logo_file" accept="image/*"
                                   class="form-control @error('logo_file') is-invalid @enderror"
                                   x-on:change="
                                        removeLogo = false;
                                        const file = $event.target.files[0];
                                        if (file) { preview = URL.createObjectURL(file); } else { preview = null; }
                                   ">
                            <small class="text-muted">JPG, PNG, SVG or WebP. Max 2MB.</small>
                            @error('logo_file')<div class="invalid-feedback">{{ $message }}</div>@enderror

                            @if($companySetting->logo_path)
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="removeLogoCheck"
                                       x-on:change="removeLogo = $event.target.checked; if (removeLogo) preview = null;">
                                <label class="form-check-label" for="removeLogoCheck">Remove current logo</label>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Invoice Numbering --}}
                    <div class="card mt-4" x-data="{ pattern: @js(old('invoice_number_pattern', $companySetting->invoice_number_pattern)) }">
                        <div class="card-header"><h5 class="card-title">Invoice Numbering</h5></div>
                        <div class="card-body">
                            <label class="form-label">Invoice Number Pattern <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_number_pattern" x-model="pattern"
                                   class="form-control @error('invoice_number_pattern') is-invalid @enderror"
                                   placeholder="INV-{year}-{id:5}" required>
                            @error('invoice_number_pattern')<div class="invalid-feedback">{{ $message }}</div>@enderror

                            <div class="mt-3">
                                <span class="text-muted fs-12">Preview for the next invoice &mdash;</span>
                                <span class="fw-semibold" x-text="formatInvoiceNumberPattern(pattern, {{ $nextInvoiceId }})">{{ $nextInvoiceNumberPreview }}</span>
                            </div>

                            <div class="mt-3 fs-12 text-muted">
                                <div class="fw-semibold mb-1">Placeholders</div>
                                <div><code>{id}</code> &mdash; sequence number (13)</div>
                                <div><code>{id:5}</code> &mdash; zero-padded sequence (00013)</div>
                                <div><code>{year}</code> &mdash; 4-digit year (2026)</div>
                                <div><code>{yy}</code> &mdash; 2-digit year (26)</div>
                                <div><code>{month}</code> &mdash; 2-digit month (08)</div>
                                <div class="mt-1">Existing invoice numbers never change &mdash; only new invoices use the updated pattern.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Mirrors App\Services\Finance\InvoiceNumberService::format() so the
    // preview updates live as the admin edits the pattern, before saving.
    function formatInvoiceNumberPattern(pattern, id) {
        if (!pattern) return '';
        const now = new Date();
        const year = String(now.getFullYear());
        const yy = year.slice(-2);
        const month = String(now.getMonth() + 1).padStart(2, '0');

        const withId = pattern.replace(/\{id(?::(\d+))?\}/g, (_, pad) => {
            return pad ? String(id).padStart(parseInt(pad, 10), '0') : String(id);
        });

        return withId
            .replaceAll('{year}', year)
            .replaceAll('{yy}', yy)
            .replaceAll('{month}', month);
    }
</script>
@endpush
@endsection
