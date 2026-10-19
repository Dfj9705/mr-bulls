<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class HeroSlide extends Model
{
    protected $fillable = ['eyebrow', 'title', 'highlight', 'description', 'image', 'image_alt', 'primary_label', 'primary_url', 'secondary_label', 'secondary_url', 'brand_line', 'sort_order', 'is_active'];

    protected $casts = ['sort_order' => 'integer', 'is_active' => 'boolean'];

    public static function validLink(?string $value): bool
    {
        if (!$value) { return true; }
        if (preg_match('/[\x00-\x20\\\\]/', $value)) { return false; }
        return str_starts_with($value, '#') || (str_starts_with($value, '/') && !str_starts_with($value, '//'))
            || (filter_var($value, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($value, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true));
    }

    protected static function booted(): void
    {
        static::saving(function (HeroSlide $slide) {
            foreach (['primary', 'secondary'] as $button) {
                $url = $slide->{$button . '_url'};
                $label = $slide->{$button . '_label'};
                if (!self::validLink($url) || (filled($label) !== filled($url))) {
                    throw ValidationException::withMessages([$button . '_url' => 'Ingresa un texto y un enlace válido para el botón.']);
                }
            }
        });
    }
}
