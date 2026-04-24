<?php

namespace App\Http\Requests\Master;

use Illuminate\Validation\Rule;

class UpdatePriorityRequest extends StorePriorityRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $priority = $this->route('priority');

        return [
            'name' => ['required', 'string', 'max:50'],
            'level' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('ticket_priorities', 'level')->ignore($priority),
            ],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
