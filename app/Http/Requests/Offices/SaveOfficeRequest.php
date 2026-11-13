<?php

namespace App\Http\Requests\Offices;

use App\Models\Office;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveOfficeRequest extends FormRequest
{
    private const LATITUDE_FORMAT = '/^-?\d{1,2}(\.\d{1,7})?$/';

    private const LONGITUDE_FORMAT = '/^-?\d{1,3}(\.\d{1,7})?$/';

    // The route is limited to the Super Admin role (D-0029).
    public function authorize(): bool
    {
        return true;
    }

    /** Text is trimmed, and a well-formed coordinate gets seven decimals, so "-6.12" and "-6.1200000" are the same place when compared. */
    protected function prepareForValidation(): void
    {
        foreach (['name', 'address'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }

        foreach (['latitude' => self::LATITUDE_FORMAT, 'longitude' => self::LONGITUDE_FORMAT] as $field => $format) {
            $value = $this->input($field);

            if (is_string($value) && preg_match($format, trim($value)) === 1) {
                $this->merge([$field => number_format((float) trim($value), 7, '.', '')]);
            }
        }
    }

    /**
     * Only the name is required. The coordinates come as a pair (both or neither), as decimal degrees with at most seven decimals (about a
     * centimetre, the precision of the columns), and no two offices share a pair.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $office = $this->route('office');
        $ignored = $office instanceof Office ? $office->id : null;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('offices', 'name')->ignore($ignored)],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => [
                'nullable',
                'required_with:longitude',
                'string',
                'regex:'.self::LATITUDE_FORMAT,
                'numeric',
                'between:-90,90',
                Rule::unique('offices', 'latitude')->where(fn (Builder $query) => $query->where('longitude', $this->input('longitude')))->ignore($ignored),
            ],
            'longitude' => ['nullable', 'required_with:latitude', 'string', 'regex:'.self::LONGITUDE_FORMAT, 'numeric', 'between:-180,180'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['latitude.unique' => 'Koordinat ini sudah dipakai kantor lain.'];
    }
}
