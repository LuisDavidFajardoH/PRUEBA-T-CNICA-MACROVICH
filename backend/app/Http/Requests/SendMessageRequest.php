<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'content' => 'required|string|max:2000',
            'metadata' => 'nullable|array',
            'metadata.*' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'content.required' => 'El contenido del mensaje es obligatorio.',
            'content.max' => 'El mensaje no puede exceder 2000 caracteres.',
            'metadata.array' => 'Los metadatos deben ser un objeto válido.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Sanitize content
        if ($this->has('content')) {
            $this->merge([
                'content' => trim($this->input('content'))
            ]);
        }
    }
}
