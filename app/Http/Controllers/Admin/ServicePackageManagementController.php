<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServicePackage;

class ServicePackageManagementController extends Controller
{
    protected int $perPage;

    public function __construct()
    {
        $this->middleware('can:manage-users');

        $this->perPage = config("paginate")["per_page"] ?? 10;
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = ServicePackage::query();

        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $packages = $query->orderBy('id', 'desc')->paginate($this->perPage);

        return view('admin.service-package-management.index', compact(var_name: 'packages'));
    }

    public function show(int $id)
    {
        $package = ServicePackage::with(['userServicePackages.user'])->findOrFail($id);

        return view('admin.service-package-management.show', [
            'package' => $package
        ]);
    }
}
