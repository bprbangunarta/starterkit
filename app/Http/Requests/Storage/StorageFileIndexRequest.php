<?php

namespace App\Http\Requests\Storage;

use App\Rules\StoragePath;
use Illuminate\Foundation\Http\FormRequest;

class StorageFileIndexRequest extends FormRequest
{
    private const SORTS = ['name', 'kind', 'size', 'modified'];

    private const PER_PAGE_OPTIONS = [25, 50, 100];

    // The route is limited to the Super Admin role (D-0029).
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'path' => ['nullable', new StoragePath],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function folder(): string
    {
        return (string) $this->validated('path');
    }

    /**
     * Sort key, direction, and page size outside the allowed values fall back to the defaults instead of failing.
     *
     * @return array{search: ?string, sort: string, direction: string, per_page: int, page: int}
     */
    public function filters(): array
    {
        $sort = (string) $this->query('sort');
        $perPage = (int) $this->query('per_page');

        return [
            'search' => $this->validated('search'),
            'sort' => in_array($sort, self::SORTS, true) ? $sort : 'name',
            'direction' => $this->query('direction') === 'desc' ? 'desc' : 'asc',
            'per_page' => in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 50,
            'page' => max(1, (int) $this->validated('page')),
        ];
    }
}
