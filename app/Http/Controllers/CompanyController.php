<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompanyResource;
use App\Http\Resources\ListingResource;
use App\Http\Resources\TagResource;
use App\Models\Company;
use App\Models\Listing;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        // return ajax
        if ($request->wantsJson()) {
            return CompanyResource::collection(
                Company::where('name', 'like', '%' . $request->q . '%')
                    ->latest()
                    ->paginate(10),
            );
        }

        return Inertia::render('Company/Index', [
            'q' => $request->has('q') ? $request->q : '',
            'companies' => CompanyResource::collection(
                Company::where('name', 'like', '%' . $request->q . '%')
                    ->latest()
                    ->paginate(10),
            ),
        ]);
    }

    public function show(Request $request, Company $company)
    {
        $listingsQuery = $company->listings();

        // filter by tags
        if ($request->has('tags') && $request->get('tags')) {
            foreach (explode(',', $request->get('tags')) as $tag) {
                $listingsQuery->whereHas('tags', function ($q) use ($tag) {
                    $q->where('slug', $tag);
                });
            }
        }

        // filter by min salary
        if ($request->has('min_salary') && $request->get('min_salary')) {
            $listingsQuery->where('min_annual_salary', '>=', $request->get('min_salary'));
        }

        // filter by closed attribute
        if ($request->has('hide_closed') && $request->get('hide_closed')) {
            $listingsQuery->where('is_closed', '!=', true);
        }

        $salaryRange = Listing::salaryRange();

        return Inertia::render('Company/Show', [
            'company' => new CompanyResource($company),
            'filters' => $request->only(['tags', 'min_salary', 'hide_closed']),
            'listings' => ListingResource::collection(
                $listingsQuery->where('paid_at', '!=', null)
                    ->orderBy('is_pinned', 'desc')
                    ->latest('paid_at')
                    ->with(['user', 'company', 'tags'])
                    ->get()
            ),
            'tags' => TagResource::collection(Tag::all()),
            'salaryRange' => [
                'min' => $salaryRange[0],
                'max' => $salaryRange[count($salaryRange) - 1],
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('Company/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_description' => 'required|string|max:255',
            'company_email' => 'required|string|email|max:255',
            'company_url' => 'required|string|url|max:255',
            'company_logo' => 'nullable|image|max:2048',
            'company_invoice_address' => 'required|string|max:255',
            'company_invoice_notes' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        $company = $user->companies()->create([
            'name' => $request->company_name,
            'description' => $request->company_description,
            'email' => $request->company_email,
            'url' => $request->company_url,
            'invoice_address' => $request->company_invoice_address,
            'invoice_notes' => $request->company_invoice_notes,
        ]);

        // handle logo
        if ($file = $request->file('company_logo')) {
            $company->update([
                'logo' => Storage::putFileAs(
                    'logos',
                    $file,
                    $file->hashName()
                )
            ]);
        }

        return redirect()->route('company.indexOwned');
    }

    public function edit(Request $request, Company $company)
    {
        return Inertia::render('Company/Edit', [
            'company' => new CompanyResource($company)
        ]);
    }

    public function update(Request $request, Company $company)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_description' => 'required|string|max:255',
            'company_email' => 'required|string|email|max:255',
            'company_url' => 'required|string|url|max:255',
            'company_logo' => 'nullable|image|max:2048',
            'company_invoice_address' => 'required|string|max:255',
            'company_invoice_notes' => 'nullable|string|max:255',
        ]);

        $company->update([
            'name' => $request->company_name,
            'description' => $request->company_description,
            'email' => $request->company_email,
            'url' => $request->company_url,
            'invoice_address' => $request->company_invoice_address,
            'invoice_notes' => $request->company_invoice_notes,
        ]);

        // handle logo
        if ($file = $request->file('company_logo')) {
            $company->update([
                'logo' => Storage::putFileAs(
                    'logos',
                    $file,
                    $file->hashName()
                )
            ]);
        } else {
            if (!$request->company_current_logo) {
                $company->update([
                    'logo' => null
                ]);
            }
        }

        return redirect()->route('company.indexOwned');
    }

    public function indexOwned(Request $request)
    {
        // return ajax
        if ($request->wantsJson()) {
            return CompanyResource::collection(
                Auth::user()->companies()
                    ->where('name', 'like', '%' . $request->q . '%')
                    ->latest()
                    ->paginate(3),
            );
        }

        return Inertia::render('Company/IndexOwned', [
            'q' => $request->has('q') ? $request->q : '',
            'companies' => CompanyResource::collection(
                Auth::user()->companies()
                    ->where('name', 'like', '%' . $request->q . '%')
                    ->latest()
                    ->paginate(3),
            ),
        ]);
    }
}
