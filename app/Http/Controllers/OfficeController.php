<?php

namespace App\Http\Controllers;

use App\Actions\Offices\DeleteOffice;
use App\Http\Requests\Offices\OfficeIndexRequest;
use App\Http\Requests\Offices\SaveOfficeRequest;
use App\Http\Resources\OfficeResource;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class OfficeController extends Controller
{
    public function index(OfficeIndexRequest $request): Response
    {
        $filters = $request->filters();

        $offices = Office::query()
            ->withCount('users')
            ->filtered($filters)
            ->sortedBy($filters['sort'], $filters['direction'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        return Inertia::render('offices', ['offices' => OfficeResource::collection($offices), 'filters' => $filters]);
    }

    public function store(SaveOfficeRequest $request): RedirectResponse
    {
        Office::query()->create($request->validated());

        return back();
    }

    public function update(SaveOfficeRequest $request, Office $office): RedirectResponse
    {
        $office->update($request->validated());

        return back();
    }

    public function destroy(Office $office, DeleteOffice $delete): RedirectResponse
    {
        $delete->handle($office);

        return back();
    }
}
