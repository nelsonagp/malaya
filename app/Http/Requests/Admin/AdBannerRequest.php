<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position' => ['required', Rule::in(['header_banner', 'sidebar', 'homepage_hero', 'footer'])],
            'image' => [$this->isMethod('post') ? 'required' : 'nullable', 'image', 'max:4096'],
            'link_url' => ['required', 'url', 'max:2048'],
            'alt_text' => ['required', 'string', 'max:255'],
            'advertiser_name' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'price_per_month' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'position.required' => 'Selecciona la posición del banner.',
            'image.required' => 'Sube una imagen para el banner.',
            'link_url.required' => 'La URL de destino es obligatoria.',
            'alt_text.required' => 'El texto alternativo es obligatorio para accesibilidad.',
            'ends_at.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
        ];
    }
}
