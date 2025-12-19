<?php

namespace App\Http\Requests\Settings;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'avatar' => [
                'nullable', // Make it optional, so the user doesn't have to upload an avatar
                'image', // Ensure the file is an image
                'mimes:jpg,jpeg,png,gif', // Allow only these file types
                'max:20480',
            ],
            'school_id' => ['required', 'string', 'max:50'], // Add this line
        ];
    }
    
    
}
