<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <i class="icon-sliders-h text-primary"></i>
            <h5 class="card-title mb-0">{{ trans('lavero-admin-config::config.settings_form') }}</h5>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('config.builder') }}" class="btn btn-sm btn-outline-primary">
                <i class="icon-tools me-1"></i> {{ trans('lavero-admin-config::config.form_builder') }}
            </a>
        </div>
    </div>

    @if($fields->isEmpty())
        <div class="card-body text-center py-5">
            <div class="mb-3 text-muted" style="font-size: 2.5rem;">
                <i class="icon-sliders-h"></i>
            </div>
            <h5 class="fw-semibold text-dark mb-1">{{ trans('lavero-admin-config::config.empty_title') }}</h5>
            <p class="text-muted small mb-3 mx-auto" style="max-width: 440px;">{{ trans('lavero-admin-config::config.empty_desc') }}</p>
            <a href="{{ route('config.builder') }}" class="btn btn-primary btn-sm px-3">
                <i class="icon-tools me-1"></i> {{ trans('lavero-admin-config::config.open_builder') }}
            </a>
        </div>
    @else
        <form action="{{ route('config.save-form') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="card-body">
                @if($tabs->count() > 1)
                    <ul class="nav nav-tabs mb-4" id="configSettingsTabs" role="tablist">
                        @foreach($tabs as $tabName => $tabFields)
                            @php $tabSlug = 'cfg-tab-' . $loop->index; @endphp
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $tabSlug }}-btn" data-bs-toggle="tab" data-bs-target="#{{ $tabSlug }}" type="button" role="tab">
                                    {{ $tabName }}
                                    <span class="badge bg-secondary ms-1">{{ $tabFields->count() }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <div class="tab-content">
                    @foreach($tabs as $tabName => $tabFields)
                        @php $tabSlug = 'cfg-tab-' . $loop->index; @endphp
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $tabSlug }}" role="tabpanel">
                            @foreach($tabFields as $field)
                                @php
                                    $opts = (array) ($field->options ?? []);
                                    $type = $field->type ?: 'text';
                                    $label = $field->label ?: $field->name;
                                    $placeholder = $opts['placeholder'] ?? '';
                                    $required = !empty($opts['required']);
                                    $fieldName = "fields[{$field->id}]";
                                    $inputId = "cfg_field_{$field->id}";
                                @endphp
                                <div class="row mb-3 pb-3 border-bottom border-light align-items-start">
                                    <div class="col-md-4 col-lg-3 mb-2 mb-md-0">
                                        <label for="{{ $inputId }}" class="form-label fw-semibold mb-1 {{ $required ? 'asterisk' : '' }}">
                                            {{ $label }}
                                        </label>
                                        <div>
                                            <code class="small text-muted cfg-copy-key" role="button" title="config('{{ $field->name }}')" data-key="config('{{ $field->name }}')">{{ $field->name }}</code>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-lg-9">
                                        @if($type === 'switch')
                                            <div class="form-check form-switch fs-5 mt-1">
                                                <input class="form-check-input" type="checkbox" name="{{ $fieldName }}" value="1" id="{{ $inputId }}" {{ filter_var($field->value, FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                                            </div>
                                        @elseif($type === 'textarea' || $type === 'json')
                                            <textarea name="{{ $fieldName }}" id="{{ $inputId }}" rows="{{ (int) ($opts['rows'] ?? 4) }}" class="form-control {{ $type === 'json' ? 'font-monospace small' : '' }}" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}>{{ $field->value }}</textarea>
                                        @elseif($type === 'select')
                                            @php $choices = $field->getParsedChoices(); @endphp
                                            <select name="{{ $fieldName }}" id="{{ $inputId }}" class="form-select" {{ $required ? 'required' : '' }}>
                                                @foreach($choices as $optVal => $optLabel)
                                                    <option value="{{ $optVal }}" {{ (string) $field->value === (string) $optVal ? 'selected' : '' }}>{{ $optLabel }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($type === 'radio')
                                            @php $choices = $field->getParsedChoices(); @endphp
                                            <div class="d-flex flex-wrap gap-3 pt-1">
                                                @foreach($choices as $optVal => $optLabel)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="{{ $fieldName }}" id="{{ $inputId }}_{{ $loop->index }}" value="{{ $optVal }}" {{ (string) $field->value === (string) $optVal ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="{{ $inputId }}_{{ $loop->index }}">{{ $optLabel }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif($type === 'checkbox')
                                            @php
                                                $choices = $field->getParsedChoices();
                                                $selected = $field->getTypedValue();
                                                if (!is_array($selected)) { $selected = []; }
                                            @endphp
                                            <div class="d-flex flex-wrap gap-3 pt-1">
                                                @foreach($choices as $optVal => $optLabel)
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="{{ $fieldName }}[]" id="{{ $inputId }}_{{ $loop->index }}" value="{{ $optVal }}" {{ in_array((string) $optVal, array_map('strval', $selected), true) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="{{ $inputId }}_{{ $loop->index }}">{{ $optLabel }}</label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif($type === 'color')
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" name="{{ $fieldName }}" id="{{ $inputId }}" class="form-control form-control-color" value="{{ $field->value ?: '#bef264' }}">
                                                <span class="small font-monospace text-muted">{{ $field->value ?: '#bef264' }}</span>
                                            </div>
                                        @elseif($type === 'number')
                                            <input type="number" name="{{ $fieldName }}" id="{{ $inputId }}" class="form-control" value="{{ $field->value }}" placeholder="{{ $placeholder }}"
                                                @if(isset($opts['min']) && $opts['min'] !== null) min="{{ $opts['min'] }}" @endif
                                                @if(isset($opts['max']) && $opts['max'] !== null) max="{{ $opts['max'] }}" @endif
                                                @if(isset($opts['step']) && $opts['step'] !== null) step="{{ $opts['step'] }}" @endif
                                                {{ $required ? 'required' : '' }}>
                                        @elseif($type === 'password')
                                            <input type="password" name="{{ $fieldName }}" id="{{ $inputId }}" class="form-control" value="" autocomplete="new-password" placeholder="{{ $placeholder ?: trans('lavero-admin-config::config.keep_password') }}">
                                        @elseif($type === 'date')
                                            <input type="date" name="{{ $fieldName }}" id="{{ $inputId }}" class="form-control" value="{{ $field->value }}" {{ $required ? 'required' : '' }}>
                                        @elseif($type === 'datetime')
                                            <input type="datetime-local" name="{{ $fieldName }}" id="{{ $inputId }}" class="form-control" value="{{ $field->value }}" {{ $required ? 'required' : '' }}>
                                        @elseif($type === 'image' || $type === 'file')
                                            @if(!empty($field->value))
                                                <div class="mb-2 d-flex align-items-center gap-3">
                                                    @if($type === 'image')
                                                        <img src="{{ \Illuminate\Support\Facades\Storage::disk(config('admin.upload.disk', 'public'))->url($field->value) }}" alt="" class="rounded border" style="max-height: 64px;">
                                                    @else
                                                        <span class="badge bg-light text-dark border font-monospace">{{ $field->value }}</span>
                                                    @endif
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="clear_fields[{{ $field->id }}]" value="1" id="clear_{{ $field->id }}">
                                                        <label class="form-check-label small text-danger" for="clear_{{ $field->id }}">{{ trans('lavero-admin-config::config.clear_file') }}</label>
                                                    </div>
                                                </div>
                                            @endif
                                            <input type="file" name="files[{{ $field->id }}]" id="{{ $inputId }}" class="form-control" {{ $type === 'image' ? 'accept=image/*' : '' }}>
                                        @else
                                            <input type="{{ in_array($type, ['email', 'url'], true) ? $type : 'text' }}" name="{{ $fieldName }}" id="{{ $inputId }}" class="form-control" value="{{ $field->value }}" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}>
                                        @endif

                                        @if(!empty($field->description))
                                            <div class="form-text text-muted">{{ $field->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="icon-save me-1"></i> {{ trans('lavero-admin-config::config.save_settings') }}
                </button>
            </div>
        </form>
    @endif
</div>

<script>
document.querySelectorAll('.cfg-copy-key').forEach(function (el) {
    el.addEventListener('click', function () {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(this.dataset.key).then(function () {
                if (window.admin && admin.toastr) {
                    admin.toastr.success('{{ trans("lavero-admin-config::config.copied") }}');
                }
            });
        }
    });
});
</script>
