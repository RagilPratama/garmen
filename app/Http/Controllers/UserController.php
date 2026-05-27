<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('toko');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Pagination
        $perPage = $request->get('per_page', 50);
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return Inertia::render('User/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role', 'per_page']),
            'roles' => [
                ['value' => User::ROLE_SUPERADMIN, 'label' => 'Super Admin'],
                ['value' => User::ROLE_ADMIN_GUDANG, 'label' => 'Admin Garmen'],
                ['value' => User::ROLE_ADMIN_KANTOR, 'label' => 'Admin Kantor'],
                ['value' => User::ROLE_ADMIN_JOMEI, 'label' => 'Admin Jomei'],
                ['value' => User::ROLE_ADMIN_KAMIKO, 'label' => 'Admin Kamiko'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', Rule::in([
                User::ROLE_SUPERADMIN,
                User::ROLE_ADMIN_GUDANG,
                User::ROLE_ADMIN_KANTOR,
                User::ROLE_ADMIN_JOMEI,
                User::ROLE_ADMIN_KAMIKO,
            ])],
        ]);

        // Auto-assign toko_id based on role
        $toko_id = null;
        if ($validated['role'] === User::ROLE_ADMIN_JOMEI) {
            $toko_id = Toko::where('kode_toko', 'JMI')->first()?->id;
        } elseif ($validated['role'] === User::ROLE_ADMIN_KAMIKO) {
            $toko_id = Toko::where('kode_toko', 'KMK')->first()?->id;
        }

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'toko_id' => $toko_id,
        ]);

        return redirect()->route('users.index')->with('message', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => [
                'nullable',
                'string',
                'min:8',
                Rule::when($request->filled('password'), ['confirmed']),
            ],
            'role' => ['required', Rule::in([
                User::ROLE_SUPERADMIN,
                User::ROLE_ADMIN_GUDANG,
                User::ROLE_ADMIN_KANTOR,
                User::ROLE_ADMIN_JOMEI,
                User::ROLE_ADMIN_KAMIKO,
            ])],
        ]);

        // Auto-assign toko_id based on role
        $toko_id = null;
        if ($validated['role'] === User::ROLE_ADMIN_JOMEI) {
            $toko_id = Toko::where('kode_toko', 'JMI')->first()?->id;
        } elseif ($validated['role'] === User::ROLE_ADMIN_KAMIKO) {
            $toko_id = Toko::where('kode_toko', 'KMK')->first()?->id;
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->toko_id = $toko_id;

        // Update password only if provided
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('message', 'User berhasil diupdate.');
    }

    public function destroy(User $user)
    {
        // Prevent deleting own account
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('message', 'User berhasil dihapus.');
    }
}
