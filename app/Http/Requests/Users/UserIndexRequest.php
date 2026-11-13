<?php

namespace App\Http\Requests\Users;

use App\Enums\UserListStatus;
use Illuminate\Foundation\Http\FormRequest;

class UserIndexRequest extends FormRequest
{
    private const SORTS = ['name', 'username', 'email', 'role', 'office', 'status', 'created'];

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
            'role' => ['nullable', 'string', 'max:100'],
            'office' => ['nullable', 'string', 'max:20'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Sort key, direction, and page size outside the allowed values fall back to the defaults instead of failing.
     *
     * @return array{search: ?string, role: ?string, office: ?string, status: string, sort: string, direction: string, per_page: int}
     */
    public function filters(): array
    {
        $sort = (string) $this->query('sort');
        $perPage = (int) $this->query('per_page');

        return [
            'search' => $this->validated('search'),
            'role' => $this->validated('role'),
            'office' => $this->officeFilter(),
            'status' => (UserListStatus::tryFrom((string) $this->query('status')) ?? UserListStatus::Active)->value,
            'sort' => in_array($sort, self::SORTS, true) ? $sort : 'name',
            'direction' => $this->query('direction') === 'desc' ? 'desc' : 'asc',
            'per_page' => in_array($perPage, self::PER_PAGE_OPTIONS, true) ? $perPage : 25,
        ];
    }

    /** An office ID, or `none` for users without an office; anything else is no filter. */
    private function officeFilter(): ?string
    {
        $office = (string) $this->validated('office');

        return $office === 'none' || ($office !== '' && ctype_digit($office)) ? $office : null;
    }
}
