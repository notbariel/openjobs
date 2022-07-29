<?php

namespace App\Http\Controllers;

use App\Enums\EmploymentType;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\ListingResource;
use App\Http\Resources\TagResource;
use App\Models\Listing;
use App\Models\Tag;
use App\Models\User;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $listingsQuery = Listing::query();

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

        // return ajax
        if ($request->wantsJson()) {
            return ListingResource::collection(
                $listingsQuery->where('paid_at', '!=', null)
                    ->orderBy('is_pinned', 'desc')
                    ->latest('paid_at')
                    ->with(['user', 'company', 'tags'])
                    ->paginate(10)
            );
        }

        $salaryRange = Listing::salaryRange();

        return Inertia::render('Listings/Index', [
            'filters' => $request->only(['tags', 'min_salary', 'hide_closed']),
            'listings' => ListingResource::collection(
                $listingsQuery->where('paid_at', '!=', null)
                    ->orderBy('is_pinned', 'desc')
                    ->latest('paid_at')
                    ->with(['user', 'company', 'tags'])
                    ->paginate(10)
            ),
            'tags' => TagResource::collection(Tag::all()),
            'salaryRange' => [
                'min' => $salaryRange[0],
                'max' => $salaryRange[count($salaryRange) - 1],
            ],
        ]);
    }

    public function show(Request $request, Listing $listing)
    {
        return Inertia::render('Listings/Show', [
            'listing' => new ListingResource(
                $listing->load(['company', 'user', 'tags'])
            ),
        ]);
    }

    public function create()
    {
        $employmentTypes = collect(EmploymentType::asSelectArray())
            ->map(function ($item) {
                return Str::title($item);
            });

        return Inertia::render('Listings/Create', [
            'salaryRange' => collect(Listing::salaryRange())->keyBy(function ($item) {
                return $item;
            })->map(function ($item) {
                return "USD " . number_format($item) . " per year";
            }),
            'employmentTypes' => $employmentTypes,
            'companies' => Auth::check() ? CompanyResource::collection(Auth::user()->companies) : []
        ]);
    }

    public function store(Request $request)
    {
        $validationRules = [];

        // company rules
        if ($request->company_id === null) {
            $validationRules = array_merge($validationRules, [
                'company_name' => 'required|string|max:255',
                'company_description' => 'required|string|max:255',
                'company_email' => 'required|string|email|max:255',
                'company_url' => 'required|string|url|max:255',
                'company_logo' => 'nullable|image|max:2048',
                'company_invoice_address' => 'required|string|max:255',
                'company_invoice_notes' => 'nullable|string|max:255',
            ]);
        }

        // job rules
        $validationRules = array_merge([
            'employment_type' => ['required', new EnumValue(EmploymentType::class, false)],
            'position' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_highlighted' => 'nullable|boolean',
            'is_pinned' => 'nullable|boolean',
            'min_annual_salary' => 'required|numeric',
            'max_annual_salary' => 'required|numeric|gte:min_annual_salary',
            'apply_url' => 'required|string|url|max:255',
            'tags' => 'required|string|max:255',
        ], $validationRules);

        // payment rules
        $validationRules = array_merge([
            'payment_method_id' => 'required',
        ], $validationRules);

        // user rules
        if (!$request->user()) {
            $validationRules = array_merge([
                'user_name' => 'required|string|max:255',
                'user_email' => 'required|string|email|max:255|unique:users,email',
                'user_password' => ['required', 'confirmed', Rules\Password::defaults()],
            ], $validationRules);
        }

        $request->validate($validationRules);

        // set user
        $user = Auth::user();

        if (!$user) {
            $user = User::create([
                'name' => $request->user_name,
                'email' => $request->user_email,
                'password' => Hash::make($request->user_password)
            ]);

            $user->createAsStripeCustomer();

            Auth::login($user);
        }

        // create/find company
        if ($request->company_id === null) {
            $company = $user->companies()->create([
                'name' => $request->company_name,
                'description' => $request->company_description,
                'email' => $request->company_email,
                'url' => $request->company_url,
                'invoice_address' => $request->company_invoice_address,
                'invoice_notes' => $request->company_invoice_notes,
            ]);
        } else {
            $company = $user->companies()
                ->where('nanoid', $request->company_id)
                ->first();
        }

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

        // process payment
        try {
            $amount = 7000; // in cents

            if ($request->is_highlighted) {
                $amount += 2500;
            }

            if ($request->is_pinned) {
                $amount += 5000;
            }

            $user->charge($amount, $request->payment_method_id);

            $listing = $user->listings()->create([
                'company_id' => $company->id,
                'employment_type' => $request->employment_type,
                'position' => $request->position,
                'location' => $request->location,
                'description' => $request->description,
                'is_highlighted' => $request->is_highlighted,
                'is_pinned' => $request->is_pinned,
                'min_annual_salary' => $request->min_annual_salary,
                'max_annual_salary' => $request->max_annual_salary,
                'apply_url' => $request->apply_url,
                'paid_at' => now(),
            ]);

            // attach tags
            foreach (explode(',', $request->tags) as $requestTag) {
                $tag = Tag::firstOrCreate([
                    'slug' => Str::slug(trim($requestTag))
                ], [
                    'name' => ucwords(trim($requestTag))
                ]);

                $tag->listings()->attach($listing->id);
            }

            return redirect()->route('home');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(Request $request, Listing $listing)
    {
        $employmentTypes = collect(EmploymentType::asSelectArray())
            ->map(function ($item) {
                return Str::title($item);
            });

        return Inertia::render('Listings/Edit', [
            'listing' => new ListingResource($listing->load(['company', 'tags'])),
            'salaryRange' => collect(Listing::salaryRange())->keyBy(function ($item) {
                return $item;
            })->map(function ($item) {
                return "USD " . number_format($item) . " per year";
            }),
            'employmentTypes' => $employmentTypes,
            'companies' => Auth::check() ? CompanyResource::collection(Auth::user()->companies) : []
        ]);
    }

    public function update(Request $request, Listing $listing)
    {
        $validationRules = [];

        // company rules
        if ($request->company_id === null) {
            $validationRules = array_merge($validationRules, [
                'company_name' => 'required|string|max:255',
                'company_description' => 'required|string|max:255',
                'company_email' => 'required|string|email|max:255',
                'company_url' => 'required|string|url|max:255',
                'company_logo' => 'nullable|image|max:2048',
                'company_invoice_address' => 'required|string|max:255',
                'company_invoice_notes' => 'nullable|string|max:255',
            ]);
        }

        // job rules
        $validationRules = array_merge([
            'employment_type' => ['required', new EnumValue(EmploymentType::class, false)],
            'position' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
            'min_annual_salary' => 'required|numeric',
            'max_annual_salary' => 'required|numeric|gte:min_annual_salary',
            'apply_url' => 'required|string|url|max:255',
            'tags' => 'required|string|max:255',
            'is_closed' => 'nullable|boolean',
        ], $validationRules);

        $request->validate($validationRules);

        // set user
        $user = Auth::user();

        // create/find company
        if ($request->company_id === null) {
            $company = $user->companies()->create([
                'name' => $request->company_name,
                'description' => $request->company_description,
                'email' => $request->company_email,
                'url' => $request->company_url,
                'invoice_address' => $request->company_invoice_address,
                'invoice_notes' => $request->company_invoice_notes,
            ]);
        } else {
            $company = $user->companies()
                ->where('nanoid', $request->company_id)
                ->first();
        }

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

        // update listing
        $listing->update([
            'company_id' => $company->id,
            'employment_type' => $request->employment_type,
            'position' => $request->position,
            'location' => $request->location,
            'description' => $request->description,
            'min_annual_salary' => $request->min_annual_salary,
            'max_annual_salary' => $request->max_annual_salary,
            'apply_url' => $request->apply_url,
            'is_closed' => $request->is_closed,
        ]);

        // sync tags
        $newTags = [];
        foreach (explode(',', $request->tags) as $requestTag) {
            $tag = Tag::firstOrCreate([
                'slug' => Str::slug(trim($requestTag))
            ], [
                'name' => ucwords(trim($requestTag))
            ]);

            $newTags[] = $tag->id;
        }
        $listing->tags()->sync($newTags);

        return redirect()->route('listing.indexOwned');
    }

    public function apply(Request $request, Listing $listing)
    {
        // abort if listing is closed or unpaid
        abort_if($listing->is_closed || $listing->paid_at === null, 404);

        $listing->clicks()->create([
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
        ]);

        return redirect()->to($listing->apply_url);
    }

    public function indexOwned(Request $request)
    {
        $listingsQuery = Auth::user()->listings();

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

        // return ajax
        if ($request->wantsJson()) {
            return ListingResource::collection(
                $listingsQuery->where('paid_at', '!=', null)
                    ->orderBy('is_pinned', 'desc')
                    ->latest('paid_at')
                    ->with(['user', 'company', 'tags'])
                    ->paginate(10)
            );
        }

        $salaryRange = Listing::salaryRange();

        return Inertia::render('Listings/IndexOwned', [
            'filters' => $request->only(['tags', 'min_salary', 'hide_closed']),
            'listings' => ListingResource::collection(
                $listingsQuery->where('paid_at', '!=', null)
                    ->orderBy('is_pinned', 'desc')
                    ->latest('paid_at')
                    ->with(['user', 'company', 'tags'])
                    ->paginate(10)
            ),
            'tags' => TagResource::collection(Tag::all()),
            'salaryRange' => [
                'min' => $salaryRange[0],
                'max' => $salaryRange[count($salaryRange) - 1],
            ],
        ]);
    }
}
