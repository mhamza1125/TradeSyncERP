<div class="row justify-content-center">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Color Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label">Color Name <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           placeholder="e.g. Navy, Burgundy, Off-White..."
                           value="{{ old('name', $color->name ?? '') }}">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>
</div>
