<?php

namespace LaveroAdmin\Admin\Config;

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
     * Index interface.
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header(trans('lavero-admin-config::config.config'))
            ->description(trans('admin.list'))
            ->body($this->grid());
    }

    /**
     * Edit interface.
     *
     * @param int     $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header(trans('lavero-admin-config::config.config'))
            ->description(trans('admin.edit'))
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     *
     * @return Content
     */
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
                $show->value();
                $show->description();
                $show->created_at();
                $show->updated_at();
            }));
    }

    public function grid()
    {
        $grid = new Grid(new ConfigModel());

        $grid->id('ID')->sortable();
        $grid->column('name', trans('lavero-admin-config::config.name'))->display(function ($name) {
            return "<a tabindex=\"0\" class=\"badge bg-primary prevent-tr text-decoration-none\" role=\"button\" data-bs-toggle=\"popover\"  title=\"" . (trans('admin.usage') ?: 'Usage') . "\" data-bs-html=\"true\" data-bs-content=\"<code>config('$name');</code>\">$name</a>";
        });
        $grid->column('value', trans('lavero-admin-config::config.value'));
        $grid->column('description', trans('lavero-admin-config::config.description'));

        $grid->created_at();
        $grid->updated_at();

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('name');
            $filter->like('value');
        });

        return $grid;
    }

    public function form()
    {
        $form = new Form(new ConfigModel());

        $form->display('id', __('admin.id'));
        $form->text('name', trans('lavero-admin-config::config.name'))->rules('required');
        if (config('admin.extensions.config.valueEmptyStringAllowed', false)) {
            $form->textarea('value', trans('lavero-admin-config::config.value'));
        } else {
            $form->textarea('value', trans('lavero-admin-config::config.value'))->rules('required');
        }
        $form->textarea('description', trans('lavero-admin-config::config.description'));

        $form->display('created_at');
        $form->display('updated_at');

        return $form;
    }
}
