<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->devices()->pluck('id')->contains($this->input('device_id'));
    }

    public function rules(): array
    {
        return [
            'content' => 'required|string|max:5000',
            'device_id' => 'required'
        ];
    }
}
