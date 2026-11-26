<?php

namespace LaveroAdmin\Admin\Config;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use LaveroAdmin\Admin\Controllers\HasResourceActions;
use LaveroAdmin\Admin\Facades\Admin;
use LaveroAdmin\Admin\Form;
use LaveroAdmin\Admin\Grid;
use LaveroAdmin\Admin\Layout\Content;
use LaveroAdmin\Admin\Show;

class ConfigController
{
    use HasResourceActions;

    /**
     * Settings Form (main view).
     */
    public function index(Content $content): Content
    {
        $fields = ConfigModel::query()->orderBy('sort')->orderBy('id')->get();
        $defaultTab = trans('lavero-admin-config::config.default_tab');

        $tabs = $fields->groupBy(function (ConfigModel $field) use ($defaultTab) {
            $tab = trim((string) $field->tab);
            return $tab !== '' ? $tab : $defaultTab;
        });

        return $content
            ->header(trans('lavero-admin-config::config.config'))
            ->description(trans('lavero-admin-config::config.settings_form'))
            ->body(view('lavero-admin-config::form', [
                'tabs'   => $tabs,
                'fields' => $fields,
            ])->render());
    }

    /**
     * Save all settings from the Settings Form.
     */
    public function saveForm(Request $request): RedirectResponse
    {
        $fields = ConfigModel::query()->orderBy('sort')->orderBy('id')->get();
        $values = (array) $request->input('fields', []);
        $clearFiles = (array) $request->input('clear_fields', []);
        $disk = config('admin.upload.disk', 'public');

        foreach ($fields as $field) {
            $idKey = (string) $field->id;
            $type = $field->type ?: 'text';

            if ($type === 'switch') {
                $field->value = !empty($values[$idKey]) ? '1' : '0';
                $field->save();
                continue;
            }

            if ($type === 'checkbox') {
                $selected = array_values(array_filter((array) ($values[$idKey] ?? []), fn ($v) => $v !== null && $v !== ''));
                $field->value = json_encode($selected, JSON_UNESCAPED_UNICODE);
                $field->save();
                continue;
            }

            if ($type === 'password') {
                $newVal = $values[$idKey] ?? null;
                if ($newVal !== null && $newVal !== '') {
                    $field->value = (string) $newVal;
                    $field->save();
                }
                continue;
            }

            if (in_array($type, ['image', 'file'], true)) {
                if (!empty($clearFiles[$idKey])) {
                    $field->value = '';
                    $field->save();
                }

                if ($request->hasFile("files.{$idKey}")) {
                    $uploaded = $request->file("files.{$idKey}");
                    if ($uploaded && $uploaded->isValid()) {
                        $path = $uploaded->store('config', $disk);
                        $field->value = $path;
                        $field->save();
                    }
                }
                continue;
            }

            if (array_key_exists($idKey, $values)) {
                $field->value = $values[$idKey];
                $field->save();
            }
        }

        ConfigModel::flushCache();
        admin_toastr(trans('lavero-admin-config::config.saved_success'), 'success');

        return redirect()->route('config.index');
    }

    /**
     * Visual Form Builder view.
     */
    public function builder(Content $content): Content
    {
        $fields = ConfigModel::query()->orderBy('sort')->orderBy('id')->get();
        $defaultTab = trans('lavero-admin-config::config.default_tab');

        $tabs = $fields->groupBy(function (ConfigModel $field) use ($defaultTab) {
            $tab = trim((string) $field->tab);
            return $tab !== '' ? $tab : $defaultTab;
        });

        if ($tabs->isEmpty()) {
            $tabs = collect([$defaultTab => collect()]);
        }

        return $content
            ->header(trans('lavero-admin-config::config.form_builder'))
            ->description(trans('lavero-admin-config::config.builder_desc'))
            ->body(view('lavero-admin-config::builder', [
                'tabs'       => $tabs,
                'fields'     => $fields,
                'fieldTypes' => ConfigModel::FIELD_TYPES,
                'defaultTab' => $defaultTab,
            ])->render());
    }

    /**
     * Create or update a setting field definition from Form Builder.
     */
    public function saveField(Request $request): RedirectResponse
    {
        $id = $request->input('id');
        $name = trim((string) $request->input('name'));

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:32',
        ]);

        $field = $id ? ConfigModel::findOrFail($id) : new ConfigModel();
        $isNew = !$field->exists;

        $type = (string) $request->input('type', 'text');
        if (!array_key_exists($type, ConfigModel::FIELD_TYPES)) {
            $type = 'text';
        }

        $options = [
            'placeholder' => trim((string) $request->input('placeholder', '')),
            'choices'     => (string) $request->input('choices', ''),
            'rows'        => max(2, (int) $request->input('rows', 4)),
            'min'         => $request->input('min', '') !== '' ? $request->input('min') : null,
            'max'         => $request->input('max', '') !== '' ? $request->input('max') : null,
            'step'        => $request->input('step', '') !== '' ? $request->input('step') : null,
            'required'    => $request->boolean('required'),
            'default'     => (string) $request->input('default', ''),
        ];

        $field->name = $name;
        $field->label = trim((string) $request->input('label', '')) ?: null;
        $field->tab = trim((string) $request->input('tab', '')) ?: null;
        $field->type = $type;
        $field->description = trim((string) $request->input('description', '')) ?: null;
        $field->sort = (int) $request->input('sort', $isNew ? ((int) ConfigModel::max('sort') + 10) : $field->sort);
        $field->options = $options;

        if ($isNew && $field->value === null) {
            $field->value = $options['default'] !== '' ? $options['default'] : ($type === 'switch' ? '0' : '');
        }

        $field->save();

        admin_toastr(trans('lavero-admin-config::config.field_saved'), 'success');

        return redirect()->route('config.builder');
    }

    /**
     * Persist drag-and-drop sort order & tab assignments.
     */
    public function saveOrder(Request $request): JsonResponse
    {
        $items = (array) $request->input('items', []);

        foreach ($items as $item) {
            if (empty($item['id'])) {
                continue;
            }
            ConfigModel::where('id', (int) $item['id'])->update([
                'sort' => (int) ($item['sort'] ?? 0),
                'tab'  => isset($item['tab']) && trim((string) $item['tab']) !== '' ? trim((string) $item['tab']) : null,
            ]);
        }

        ConfigModel::flushCache();

        return response()->json([
            'status'  => true,
            'message' => trans('lavero-admin-config::config.order_saved'),
        ]);
    }

    /**
     * Delete a setting field from Form Builder.
     */
    public function deleteField(Request $request): JsonResponse|RedirectResponse
    {
        $id = (int) $request->input('id');
        if ($id > 0) {
            ConfigModel::destroy($id);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'status'  => true,
                'message' => trans('lavero-admin-config::config.field_deleted'),
            ]);
        }

        admin_toastr(trans('lavero-admin-config::config.field_deleted'), 'success');

        return redirect()->route('config.builder');
    }

    public function edit($id, Content $content)
    {
        return $content
            ->header(trans('lavero-admin-config::config.config'))
            ->description(trans('admin.edit'))
            ->body($this->form()->edit($id));
    }

    public function create(Content $content)
    {
        return $content
            ->header(trans('lavero-admin-config::config.config'))
            ->description(trans('admin.create'))
            ->body($this->form());
    }

    public function show($id, Content $content)
    {
        return $content
            ->header(trans('lavero-admin-config::config.config'))
            ->description(trans('admin.detail'))
            ->body(Admin::show(ConfigModel::findOrFail($id), function (Show $show) {
                $show->id();
                $show->name();
                $show->label();
                $show->tab();
                $show->type();
                $show->value();
                $show->description();
                $show->sort();
                $show->created_at();
                $show->updated_at();
            }));
    }

    public function grid()
    {
        $grid = new Grid(new ConfigModel());

        $grid->id('ID')->sortable();
        $grid->column('name', trans('lavero-admin-config::config.name'));
        $grid->column('label', trans('lavero-admin-config::config.label'));
        $grid->column('tab', trans('lavero-admin-config::config.tab'));
        $grid->column('type', trans('lavero-admin-config::config.type'));
        $grid->column('value', trans('lavero-admin-config::config.value'));

        return $grid;
    }

    public function form()
    {
        $form = new Form(new ConfigModel());

        $form->display('id', __('admin.id'));
        $form->text('name', trans('lavero-admin-config::config.name'))->rules('required');
        $form->text('label', trans('lavero-admin-config::config.label'));
        $form->text('tab', trans('lavero-admin-config::config.tab'));
        $form->select('type', trans('lavero-admin-config::config.type'))->options(ConfigModel::FIELD_TYPES)->default('text');
        $form->textarea('value', trans('lavero-admin-config::config.value'));
        $form->textarea('description', trans('lavero-admin-config::config.description'));
        $form->number('sort', trans('lavero-admin-config::config.sort'))->default(0);

        $form->display('created_at');
        $form->display('updated_at');

        return $form;
    }
}
