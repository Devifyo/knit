<?php

declare(strict_types=1);

namespace App\Modules\Accounts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('accounts.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['required', 'integer', 'exists:companies,id'],
            'health_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'renewal_date' => ['nullable', 'date'],
            'renewal_status' => ['nullable', Rule::in(['pending', 'at risk', 'renewed', 'churned'])],
        ];
    }
}
