<?php

namespace App\Http\Requests\Audit;

use App\Enums\AuditResult;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuditLogIndexRequest extends FormRequest
{
    private const SORTS = ['time', 'host', 'user', 'module', 'event', 'record', 'outcome'];

    private const PER_PAGE_OPTIONS = [10, 25, 50, 100];

    // The route is limited to the Super Admin role (D-0029).
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'module' => ['nullable', 'string', 'max:100'],
            'result' => ['nullable', Rule::enum(AuditResult::class)],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Sort key, direction, and page size outside the allowed values fall back to the defaults instead of failing.
     *
     * @return array{search: ?string, module: ?string, result: ?string, from: ?string, to: ?string, sort: string, direction: string, per_page: int}
     */
    public function filters(): array
    {
        $sort = (string) $this->query('sort');
        $perPage = (int) $this->query('per_page');

        return [
            'search' => $this->validated('search'),
            'module' => $this->validated('module'),
            'result' => $this->validated('result'),
            'from' => $this->validated('from'),
            'to' => $this->validated('to'),
            'sort' => in_array($sort, self::SORTS, true) ? $sort : 'time',
            'direction' => $this->query('direction') === 'asc' ? 'asc' : 'desc',
            'per_page' => in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 25,
        ];
    }
}
