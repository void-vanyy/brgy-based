<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $setting = static::query()->where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    public static function set(string $key, $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * All settings as a key => value array.
     *
     * NB: deliberately not named `all()` — that would collide with
     * Illuminate\Database\Eloquent\Model::all() and fatal on declaration.
     */
    public static function kv(): array
    {
        return static::query()->pluck('value', 'key')->all();
    }
}
