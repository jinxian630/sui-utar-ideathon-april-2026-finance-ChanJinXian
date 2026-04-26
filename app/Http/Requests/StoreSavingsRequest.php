<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount'       => ['required', 'numeric', 'gt:0', 'max:999999.99'],
            'note'         => ['nullable', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:1000'],
            'type'         => ['required', 'in:income,expense'],
            'category'     => ['nullable', 'string', 'in:food,transport,entertainment,utilities,other'],
            'goal_name'    => ['nullable', 'string', 'max:255'],
            'goal_id'      => ['nullable', 'exists:goals,id'],
            'entry_date'   => ['nullable', 'date', 'before_or_equal:today'],
            'enable_round_up' => ['nullable', 'boolean'],
            'enable_stake' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.gt'        => 'The savings amount must be greater than zero.',
            'amount.max'       => 'Amount cannot exceed RM 999,999.99.',
            'note.max'         => 'Note must not exceed 255 characters.',
            'goal_id.exists'   => 'The selected goal does not exist.',
            'entry_date.before_or_equal' => 'Entry date cannot be in the future.',
        ];
    }
}
