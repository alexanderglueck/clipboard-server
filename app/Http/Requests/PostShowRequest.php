<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostShowRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('paste')->user_id == $this->user()->id;
    }

    public function rules(): array
    {
        return [];
    }
}
