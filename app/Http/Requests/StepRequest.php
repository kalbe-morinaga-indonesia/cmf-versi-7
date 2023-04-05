<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StepRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'verifikasi' => [
                'required'
            ],
            'catatan' => [
                'required'
            ]
        ];
    }
}
