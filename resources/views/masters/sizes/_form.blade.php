<div class="row justify-content-center">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Size Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Size Name <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="e.g. S, M, L, XL, 40, 42..."
                           value="{{ old('name', $size->name ?? '') }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
