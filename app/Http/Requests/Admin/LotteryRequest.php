<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LotteryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $lotteryId = $this->route('lottery')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('lotteries', 'slug')->ignore($lotteryId)],
            'country' => ['required', 'string', 'max:100'],
            'country_code' => ['required', 'string', 'size:2'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'website_url' => ['nullable', 'url', 'max:2048'],
            'results_url' => ['nullable', 'url', 'max:2048'],
            'scraper_class' => ['nullable', 'string', 'max:255'],
            'scraper_config' => ['nullable', 'json'],
            'draw_days' => ['nullable', 'array'],
            'draw_days.*' => ['string', Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])],
            'draw_time' => ['nullable', 'string'],
            'draw_timezone' => ['nullable', 'string', 'max:64'],
            'draw_frequency' => ['nullable', Rule::in(['daily', 'weekly', 'biweekly', 'monthly'])],
            'number_count' => ['required', 'integer', 'min:1', 'max:20'],
            'number_range_min' => ['required', 'integer', 'min:0'],
            'number_range_max' => ['required', 'integer', 'gt:number_range_min'],
            'has_series' => ['boolean'],
            'has_fractions' => ['boolean'],
            'prize_info' => ['nullable', 'string'],
            'affiliate_url' => ['nullable', 'url', 'max:2048'],
            'display_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'slug.alpha_dash' => 'El slug solo puede tener letras, números, guiones y guiones bajos.',
            'slug.unique' => 'Ya existe una lotería con este slug.',
            'country.required' => 'El país es obligatorio.',
            'country_code.required' => 'El código de país es obligatorio.',
            'country_code.size' => 'El código de país debe tener 2 letras (ej: CO).',
            'logo.image' => 'El logo debe ser una imagen.',
            'website_url.url' => 'Ingresa una URL válida.',
            'results_url.url' => 'Ingresa una URL válida.',
            'scraper_config.json' => 'La configuración del scraper debe ser JSON válido.',
            'number_range_max.gt' => 'El rango máximo debe ser mayor que el mínimo.',
        ];
    }
}
