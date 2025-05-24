<?php

namespace App\Http\Requests\V1\Escrow;

use App\Enums\Escrow\EscrowPhase;
use App\Enums\Escrow\EscrowStage;
use App\Enums\Escrow\EscrowStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetUserEscrowsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string'],
            'status' => ['nullable', Rule::enum(EscrowStatus::class)],
            'phase' => ['nullable', Rule::enum(EscrowPhase::class)],
            'stage' => ['nullable', Rule::enum(EscrowStage::class)],
            'per_page' => ['nullable', 'integer'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => $this->get('search'),
            'status' => $this->get('status'),
            'phase' => $this->get('phase'),
            'stage' => $this->get('stage'),
            'per_page' => $this->get('per_page') ?? 10,
        ]);
    }
}
