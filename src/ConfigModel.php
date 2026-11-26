<?php

namespace LaveroAdmin\Admin\Config;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ConfigModel extends Model
{
    public const CACHE_KEY = 'lavero_admin_config_all';

    public const FIELD_TYPES = [
        'text'     => 'Text',
        'textarea' => 'Textarea',
        'number'   => 'Number',
        'email'    => 'Email',
        'url'      => 'URL',
        'password' => 'Password',
        'switch'   => 'Switch (Boolean)',
        'select'   => 'Select Dropdown',
        'radio'    => 'Radio Buttons',
        'checkbox' => 'Checkboxes (Multi)',
        'color'    => 'Color Picker',
        'date'     => 'Date',
        'datetime' => 'Date & Time',
        'image'    => 'Image Upload',
        'file'     => 'File Upload',
        'json'     => 'JSON / Code',
    ];

    protected $fillable = [
        'name',
        'label',
        'tab',
        'type',
        'value',
        'options',
        'description',
        'sort',
    ];

    protected $casts = [
        'options' => 'array',
        'sort'    => 'integer',
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

        $this->setConnection(config('admin.database.connection') ?: config('database.default'));
        $this->setTable(config('admin.extensions.config.table', 'admin_config'));
    }

    public function setValueAttribute($value = null)
    {
        if (is_array($value)) {
            $this->attributes['value'] = json_encode($value, JSON_UNESCAPED_UNICODE);
            return;
        }

        if (config('admin.extensions.config.valueEmptyStringAllowed', false)) {
            $this->attributes['value'] = is_null($value) ? '' : $value;
        } else {
            $this->attributes['value'] = $value;
        }
    }

    public function getTypedValue(): mixed
    {
        $val = $this->value;
        $type = $this->type ?: 'text';

        if ($type === 'switch') {
            return filter_var($val, FILTER_VALIDATE_BOOLEAN);
        }

        if ($type === 'checkbox') {
            if (is_array($val)) {
                return $val;
            }
            $decoded = json_decode((string) $val, true);
            return is_array($decoded) ? $decoded : [];
        }

        if ($type === 'number' && is_numeric($val)) {
            return str_contains((string) $val, '.') ? (float) $val : (int) $val;
        }

        return $val;
    }

    /**
     * Parse choices string/array into [value => label] associative array.
     */
    public function getParsedChoices(): array
    {
        $raw = $this->options['choices'] ?? [];
        if (is_array($raw)) {
            return $raw;
        }

        $choices = [];
        $lines = preg_split('/[\r\n]+/', (string) $raw) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (str_contains($line, ':')) {
                [$k, $v] = array_map('trim', explode(':', $line, 2));
                $choices[$k] = $v;
            } else {
                $choices[$line] = $line;
            }
        }

        return $choices;
    }

    public static function flushCache(): void
    {
        try {
            Cache::forget(self::CACHE_KEY);
        } catch (\Throwable) {
        }
    }

    protected static function booted(): void
    {
        static::saved(function () {
            static::flushCache();
        });

        static::deleted(function () {
            static::flushCache();
        });
    }
}
