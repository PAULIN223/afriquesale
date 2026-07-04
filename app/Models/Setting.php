<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'cle';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['cle', 'valeur'];

    public static function get(string $key, $default = null): mixed
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::find($key);
            return $setting ? $setting->valeur : $default;
        });
    }

    public static function set(string $key, $value): void
    {
        self::updateOrCreate(['cle' => $key], ['valeur' => $value]);
        Cache::forget("setting_{$key}");
    }
}
