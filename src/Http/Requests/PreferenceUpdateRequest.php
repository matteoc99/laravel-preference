<?php

namespace Matteoc99\LaravelPreference\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PreferenceUpdateRequest extends FormRequest
{

    public function rules()
    {
        return [
            'value' => 'required',
        ];
    }

}
