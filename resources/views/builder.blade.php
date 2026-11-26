<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('config.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="icon-arrow-left me-1"></i> {{ trans('lavero-admin-config::config.back_to_form') }}
            </a>
            <button type="button" class="btn btn-sm btn-primary" id="btn-add-field">
                <i class="icon-plus me-1"></i> {{ trans('lavero-admin-config::config.add_field') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-add-tab">
                <i class="icon-folder-plus me-1"></i> {{ trans('lavero-admin-config::config.add_tab') }}
            </button>
        </div>
        <div>
            <button type="button" class="btn btn-sm btn-success text-white" id="btn-save-order">
                <i class="icon-save me-1"></i> {{ trans('lavero-admin-config::config.save_order') }}
            </button>
        </div>
    </div>

    <div class="card-body" id="builder-tabs-container">
        @foreach($tabs as $tabName => $tabFields)
            <div class="cfg-builder-tab card mb-3 border" data-tab-name="{{ $tabName }}">
                <div class="card-header bg-light py-2 px-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="icon-folder text-primary"></i>
                        <strong class="cfg-tab-title">{{ $tabName }}</strong>
                        <span class="badge bg-secondary">{{ $tabFields->count() }}</span>
                    </div>
                    <button type="button" class="btn btn-xs btn-outline-primary btn-add-field-to-tab" data-tab="{{ $tabName }}">
                        <i class="icon-plus"></i> {{ trans('lavero-admin-config::config.add_field') }}
                    </button>
                </div>
                <div class="card-body p-2">
                    <div class="cfg-sortable-list d-flex flex-column gap-2" data-tab="{{ $tabName }}" style="min-height: 38px;">
                        @foreach($tabFields as $field)
                            @php
                                $opts = (array) ($field->options ?? []);
                                $payload = [
                                    'id'          => $field->id,
                                    'name'        => $field->name,
                                    'label'       => $field->label ?? '',
                                    'tab'         => $field->tab ?? $defaultTab,
                                    'type'        => $field->type ?: 'text',
                                    'description' => $field->description ?? '',
                                    'sort'        => $field->sort,
                                    'placeholder' => $opts['placeholder'] ?? '',
                                    'choices'     => $opts['choices'] ?? '',
                                    'rows'        => $opts['rows'] ?? 4,
                                    'min'         => $opts['min'] ?? '',
                                    'max'         => $opts['max'] ?? '',
                                    'step'        => $opts['step'] ?? '',
                                    'required'    => !empty($opts['required']),
                                    'default'     => $opts['default'] ?? '',
                                ];
                            @endphp
                            <div class="cfg-builder-item d-flex flex-wrap align-items-center justify-content-between gap-2 p-2 px-3 border rounded bg-white" data-id="{{ $field->id }}">
                                <div class="d-flex align-items-center gap-2 flex-grow-1 min-w-0">
                                    <span class="cfg-drag-handle text-muted" style="cursor: grab;">
                                        <i class="icon-grip-vertical"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="fw-semibold text-dark text-truncate">
                                            {{ $field->label ?: $field->name }}
                                            @if(!empty($opts['required']))
                                                <span class="text-danger">*</span>
                                            @endif
                                        </div>
                                        <div class="small text-muted d-flex flex-wrap align-items-center gap-2">
                                            <code>{{ $field->name }}</code>
                                            <span class="badge bg-light text-secondary border">{{ $fieldTypes[$field->type ?: 'text'] ?? $field->type }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-edit-field" data-field='@json($payload)'>
                                        <i class="icon-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-field" data-id="{{ $field->id }}" data-name="{{ $field->label ?: $field->name }}">
                                        <i class="icon-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- Modal: Add / Edit Field -->
<div class="modal fade" id="modal-builder-field" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('config.builder.save-field') }}" method="POST" id="form-builder-field">
                @csrf
                <input type="hidden" name="id" id="bf-id" value="">
                <div class="modal-header">
                    <h5 class="modal-title" id="bf-modal-title">{{ trans('lavero-admin-config::config.add_field') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold asterisk">{{ trans('lavero-admin-config::config.name') }}</label>
                            <input type="text" name="name" id="bf-name" class="form-control font-monospace" placeholder="site.title" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ trans('lavero-admin-config::config.label') }}</label>
                            <input type="text" name="label" id="bf-label" class="form-control" placeholder="Название сайта">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ trans('lavero-admin-config::config.tab') }}</label>
                            <input type="text" name="tab" id="bf-tab" class="form-control" list="bf-tabs-datalist" value="{{ $defaultTab }}">
                            <datalist id="bf-tabs-datalist">
                                @foreach($tabs->keys() as $tName)
                                    <option value="{{ $tName }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ trans('lavero-admin-config::config.type') }}</label>
                            <select name="type" id="bf-type" class="form-select">
                                @foreach($fieldTypes as $tKey => $tLabel)
                                    <option value="{{ $tKey }}">{{ $tLabel }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ trans('lavero-admin-config::config.placeholder') }}</label>
                            <input type="text" name="placeholder" id="bf-placeholder" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ trans('lavero-admin-config::config.default_value') }}</label>
                            <input type="text" name="default" id="bf-default" class="form-control">
                        </div>

                        <div class="col-12" id="bf-choices-group" style="display: none;">
                            <label class="form-label fw-semibold">{{ trans('lavero-admin-config::config.choices') }}</label>
                            <textarea name="choices" id="bf-choices" rows="4" class="form-control font-monospace small" placeholder="light:Светлая&#10;dark:Тёмная"></textarea>
                            <div class="form-text">{{ trans('lavero-admin-config::config.choices_help') }}</div>
                        </div>

                        <div class="col-md-4" id="bf-rows-group" style="display: none;">
                            <label class="form-label fw-semibold">{{ trans('lavero-admin-config::config.rows') }}</label>
                            <input type="number" name="rows" id="bf-rows" class="form-control" value="4" min="2" max="30">
                        </div>

                        <div class="col-12" id="bf-number-group" style="display: none;">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ trans('lavero-admin-config::config.min') }}</label>
                                    <input type="number" name="min" id="bf-min" class="form-control" step="any">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ trans('lavero-admin-config::config.max') }}</label>
                                    <input type="number" name="max" id="bf-max" class="form-control" step="any">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">{{ trans('lavero-admin-config::config.step') }}</label>
                                    <input type="number" name="step" id="bf-step" class="form-control" step="any">
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">{{ trans('lavero-admin-config::config.description') }}</label>
                            <textarea name="description" id="bf-description" rows="2" class="form-control"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">{{ trans('lavero-admin-config::config.sort') }}</label>
                            <input type="number" name="sort" id="bf-sort" class="form-control" value="0">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="required" id="bf-required" value="1">
                                <label class="form-check-label" for="bf-required">{{ trans('lavero-admin-config::config.required') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ trans('admin.close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ trans('admin.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    const modalEl = document.getElementById('modal-builder-field');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    const typeSelect = document.getElementById('bf-type');

    function toggleTypeOptions() {
        const t = typeSelect.value;
        document.getElementById('bf-choices-group').style.display = ['select', 'radio', 'checkbox'].includes(t) ? 'block' : 'none';
        document.getElementById('bf-rows-group').style.display = ['textarea', 'json'].includes(t) ? 'block' : 'none';
        document.getElementById('bf-number-group').style.display = t === 'number' ? 'block' : 'none';
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', toggleTypeOptions);
    }

    function openFieldModal(data) {
        document.getElementById('bf-modal-title').textContent = data && data.id
            ? '{{ trans("lavero-admin-config::config.edit_field") }}'
            : '{{ trans("lavero-admin-config::config.add_field") }}';
        document.getElementById('bf-id').value = (data && data.id) || '';
        document.getElementById('bf-name').value = (data && data.name) || '';
        document.getElementById('bf-label').value = (data && data.label) || '';
        document.getElementById('bf-tab').value = (data && data.tab) || '{{ $defaultTab }}';
        document.getElementById('bf-type').value = (data && data.type) || 'text';
        document.getElementById('bf-placeholder').value = (data && data.placeholder) || '';
        document.getElementById('bf-default').value = (data && data.default) || '';
        document.getElementById('bf-choices').value = (data && data.choices) || '';
        document.getElementById('bf-rows').value = (data && data.rows) || 4;
        document.getElementById('bf-min').value = (data && data.min !== null && data.min !== undefined) ? data.min : '';
        document.getElementById('bf-max').value = (data && data.max !== null && data.max !== undefined) ? data.max : '';
        document.getElementById('bf-step').value = (data && data.step !== null && data.step !== undefined) ? data.step : '';
        document.getElementById('bf-description').value = (data && data.description) || '';
        document.getElementById('bf-sort').value = (data && data.sort !== undefined) ? data.sort : 0;
        document.getElementById('bf-required').checked = !!(data && data.required);
        toggleTypeOptions();
        if (modal) modal.show();
    }

    document.getElementById('btn-add-field')?.addEventListener('click', function () {
        openFieldModal({ tab: '{{ $defaultTab }}' });
    });

    document.addEventListener('click', function (e) {
        const addBtn = e.target.closest('.btn-add-field-to-tab');
        if (addBtn) {
            openFieldModal({ tab: addBtn.dataset.tab });
            return;
        }
        const editBtn = e.target.closest('.btn-edit-field');
        if (editBtn) {
            openFieldModal(JSON.parse(editBtn.dataset.field));
            return;
        }
        const delBtn = e.target.closest('.btn-delete-field');
        if (delBtn) {
            const msg = '{{ trans("lavero-admin-config::config.confirm_delete") }}'.replace(':name', delBtn.dataset.name);
            if (!confirm(msg)) return;
            fetch('{{ route("config.builder.delete-field") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ id: delBtn.dataset.id })
            }).then(r => r.json()).then(res => {
                if (res.status) {
                    delBtn.closest('.cfg-builder-item').remove();
                    if (window.admin && admin.toastr) admin.toastr.success(res.message);
                }
            });
        }
    });

    function initSortables() {
        if (typeof Sortable === 'undefined') return;
        document.querySelectorAll('.cfg-sortable-list').forEach(function (list) {
            if (list._cfgSortable) return;
            list._cfgSortable = new Sortable(list, {
                group: 'cfg-fields',
                animation: 150,
                handle: '.cfg-drag-handle'
            });
        });
    }
    initSortables();

    document.getElementById('btn-add-tab')?.addEventListener('click', function () {
        const tabName = prompt('{{ trans("lavero-admin-config::config.enter_tab_name") }}');
        if (!tabName || !tabName.trim()) return;
        const clean = tabName.trim();
        const container = document.getElementById('builder-tabs-container');
        const card = document.createElement('div');
        card.className = 'cfg-builder-tab card mb-3 border';
        card.dataset.tabName = clean;
        card.innerHTML = `
            <div class="card-header bg-light py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="icon-folder text-primary"></i>
                    <strong class="cfg-tab-title"></strong>
                    <span class="badge bg-secondary">0</span>
                </div>
                <button type="button" class="btn btn-xs btn-outline-primary btn-add-field-to-tab"><i class="icon-plus"></i> {{ trans('lavero-admin-config::config.add_field') }}</button>
            </div>
            <div class="card-body p-2">
                <div class="cfg-sortable-list d-flex flex-column gap-2" style="min-height: 38px;"></div>
            </div>
        `;
        card.querySelector('.cfg-tab-title').textContent = clean;
        card.querySelector('.btn-add-field-to-tab').dataset.tab = clean;
        card.querySelector('.cfg-sortable-list').dataset.tab = clean;
        container.appendChild(card);

        const opt = document.createElement('option');
        opt.value = clean;
        document.getElementById('bf-tabs-datalist')?.appendChild(opt);

        initSortables();
    });

    document.getElementById('btn-save-order')?.addEventListener('click', function () {
        const items = [];
        let sort = 10;
        document.querySelectorAll('.cfg-sortable-list').forEach(function (list) {
            const tab = list.dataset.tab || '{{ $defaultTab }}';
            list.querySelectorAll('.cfg-builder-item').forEach(function (el) {
                items.push({ id: parseInt(el.dataset.id, 10), sort: sort, tab: tab });
                sort += 10;
            });
        });

        fetch('{{ route("config.builder.save-order") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ items: items })
        }).then(r => r.json()).then(res => {
            if (res.status && window.admin && admin.toastr) {
                admin.toastr.success(res.message);
            }
        });
    });
})();
</script>
