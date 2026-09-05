<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'month'        => ['required', 'date_format:Y-m'],
            'payload_json' => ['required', 'string'],
        ];
    }

    public function getParsedPayload(): array
    {
        $raw = (string) $this->input('payload_json', '[]');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
