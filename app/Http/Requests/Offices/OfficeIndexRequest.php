<?php

namespace App\Http\Requests\Offices;

use Illuminate\Foundation\Http\FormRequest;

class OfficeIndexRequest extends FormRequest
{
    private const SORTS = ['name', 'address', 'latitude', 'longitude', 'users', 'created'];

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    // The route is limited to the Super Admin role (D-0029).
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Sort key, direction, and page size outside the allowed values fall back to the defaults instead of failing.
     *
     * @return array{search: ?string, sort: string, direction: string, per_page: int}
     */
    public function filters(): array
    {
        $sort = (string) $this->query('sort');
        $perPage = (int) $this->query('per_page');

        return [
            'search' => $this->validated('search'),
            'sort' => in_array($sort, self::SORTS, true) ? $sort : 'name',
            'direction' => $this->query('direction') === 'desc' ? 'desc' : 'asc',
            'per_page' => in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 25,
        ];
    }
}
