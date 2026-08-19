<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

use App\Http\Controllers\VitalSignController;

class UserController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')->latest()->paginate(15);

        return view('users.index', compact('users'));
    }

    public function indexCsv()
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')->orderBy('name')->get();

        $filename = 'data-pengguna-' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['DATA PENGGUNA SISTEM HIS']);
            fputcsv($handle, ['Dibuat', now()->format('d/m/Y H:i')]);
            fputcsv($handle, []);

            fputcsv($handle, ['Nama', 'Email', 'Peran', 'Status', 'Dibuat Pada']);
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->name,
                    $user->email,
                    $user->roles->pluck('name')->implode(', '),
                    $user->is_active ? 'Aktif' : 'Nonaktif',
                    $user->created_at?->format('d/m/Y H:i'),
                ]);
            }
            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL PENGGUNA', $users->count()]);

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::orderBy('name')->get();

        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::orderBy('name')->get();
        $currentRole = $user->roles->first();

        return view('users.edit', compact('user', 'roles', 'currentRole'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        $user->syncRoles($validated['role']);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function toggleActive(User $user)
    {
        $this->authorize('update', $user);

        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menonaktifkan akun sendiri.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return redirect()->route('users.index')
            ->with('success', $user->is_active ? 'User berhasil diaktifkan.' : 'User berhasil dinonaktifkan.');
    }
}
