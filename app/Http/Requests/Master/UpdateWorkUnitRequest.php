<?php

namespace App\Http\Requests\Master;

use Illuminate\Validation\Rule;

class UpdateWorkUnitRequest extends StoreWorkUnitRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $workUnit = $this->route('work_unit');

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:20', Rule::unique('work_units', 'code')->ignore($workUnit)],
        ];
    }
}
