<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LotteryResultRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $resultId = $this->route('resultado')?->id;

        return [
            'lottery_id' => ['required', 'uuid', 'exists:lotteries,id'],
            'draw_date' => [
                'required',
                'date',
                Rule::unique('lottery_results', 'draw_date')
                    ->where('lottery_id', $this->input('lottery_id'))
                    ->ignore($resultId),
            ],
            'draw_number' => ['nullable', 'integer'],
            'numbers' => ['required', 'string'],
            'prize_breakdown' => ['nullable', 'json'],
            'jackpot_amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'is_verified' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'lottery_id.required' => 'Selecciona una lotería.',
            'draw_date.required' => 'La fecha del sorteo es obligatoria.',
            'draw_date.unique' => 'Ya existe un resultado para esta lotería en esta fecha.',
            'numbers.required' => 'Ingresa los números del sorteo, separados por coma.',
            'prize_breakdown.json' => 'El desglose de premios debe ser JSON válido.',
        ];
    }
}
