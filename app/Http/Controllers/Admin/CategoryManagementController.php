<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Models\WalletType;
use App\Models\Category;

class CategoryManagementController extends Controller
{
    protected array $walletTypeConfigs;
    protected array  $typeConfig;
    protected int $perPage;

    public function __construct()
    {
        $this->middleware('can:manage-users');

        $this->walletTypeConfigs = config('wallet_types');
        $this->typeConfig = config('category_types');
        $this->perPage = config("paginate")["per_page"] ?? 10;
    }

    public function index(Request $request)
    {
        $query = Category::query()
            ->with([
                'parent:id,name',
                'creator:id,name',
                'walletTypes:id,name'
            ]);

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($walletType = $request->input('wallet_type')) {
            $query->whereHas('walletTypes', function ($q) use ($walletType) {
                $q->where('wallet_types.id', $walletType);
            });
        }

        $categories = tap($query->paginate($this->perPage))->withQueryString();

        $categories->getCollection()->transform(function ($category) {
            $wallets = $category->walletTypes ?? collect();

            $category->wallet_display = $wallets->map(function ($wallet) {
                $config = $this->walletTypeConfigs[$wallet->name] ?? [
                    'label' => $wallet->name,
                    'class' => 'text-gray-400'
                ];

                return [
                    'label' => $config['label'],
                    'class' => $config['class'],
                ];
            });

            return $category;
        });

        $typeConfig = collect($this->typeConfig);

        $walletConfig = $this->getWalletConfig();

        return view('admin.category-management.index', compact('categories', 'typeConfig', 'walletConfig'));
    }

    public function show(Category $category)
    {
        $category->load(['parent:id,name', 'creator:id,name', 'walletTypes:id,name']);

        $typeConfig = collect($this->typeConfig);

        $walletConfig = $this->getWalletConfig();

        return view('admin.category-management.show', compact('category', 'typeConfig', 'walletConfig'));
    }

    public function create()
    {
        $typeConfig = collect($this->typeConfig);

        $walletConfig = $this->getWalletConfig();

        $parents = Category::select('id', 'name')->get();

        return view('admin.category-management.create', compact('typeConfig', 'walletConfig', 'parents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'category_parent_id' => 'nullable|exists:categories,id',
            'wallet_types' => 'nullable|array',
            'wallet_types.*' => 'exists:wallet_types,id',
        ]);

        if (empty($validated['wallet_types']) || count($validated['wallet_types']) === 0) {
            return back()
                ->withErrors(['wallet_types' => __('category.wallet_types_required')])
                ->withInput();
        }

        $category = Category::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'category_parent_id' => $validated['category_parent_id'] ?? null,
            'created_by' => auth()->id(),
            'default' => true,
        ]);

        $category->walletTypes()->sync($validated['wallet_types']);

        return redirect()->route('categories.index')
            ->with('success', __('category.category_created_success'));
    }

    public function edit(Category $category)
    {
        $typeConfig = collect($this->typeConfig);
        $walletConfig = $this->getWalletConfig();
        $parents = Category::where('id', '!=', $category->id)->select('id', 'name')->get();
        $selectedWalletTypes = $category->walletTypes->pluck('id')->toArray();

        return view('admin.category-management.edit', compact(
            'category',
            'typeConfig',
            'walletConfig',
            'parents',
            'selectedWalletTypes'
        ));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'category_parent_id' => 'nullable|exists:categories,id|not_in:' . $category->id,
            'wallet_types' => 'nullable|array',
            'wallet_types.*' => 'exists:wallet_types,id',
        ]);

        if (empty($validated['wallet_types'])) {
            return back()
                ->withErrors(['wallet_types' => __('category.wallet_types_required')])
                ->withInput();
        }

        $category->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'category_parent_id' => $validated['category_parent_id'] ?? null,
        ]);

        $category->walletTypes()->sync($validated['wallet_types']);

        return redirect()->route('categories.index')
            ->with('success', __('category.category_updated_success'));
    }

    private function getWalletConfig(): Collection
    {
        $walletTypes = WalletType::all(['id', 'name']);

        return collect($walletTypes)->mapWithKeys(function ($item) {
            $config = $this->walletTypeConfigs[$item->name] ?? [
                'label' => $item->name,
                'class' => 'text-gray-400'
            ];

            return [ (string) $item->id => array_merge($config, ['name' => $item->name])];
        });
    }

    public function destroy(Category $category)
    {
        try {
            $category->delete();

            return redirect()->route('categories.index')
                ->with('success', __('category.deleted_success'));
        } catch (\Exception $e) {
            return redirect()->route('categories.index')
                ->with('error', __('category.deleted_error'));
        }
    }
}
