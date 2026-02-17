<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceTapRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cardIdentifier' => ['required', 'string', 'max:64'],
            'rfidCardIdentifier' => ['sometimes', 'string', 'max:64'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('cardIdentifier') && $this->has('rfidCardIdentifier')) {
            $this->merge(['cardIdentifier' => $this->input('rfidCardIdentifier')]);
        }
    }
}
