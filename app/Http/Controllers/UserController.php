<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Kelola Pengguna - hanya untuk role pengelola (lihat routes/web.php).
 * Pembeli tidak punya akun, jadi tidak dikelola di sini.
 */
class UserController extends Controller
{
    protected const ITEMS_PER_PAGE = 15;

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));
        $role = $request->input('role');

        $users = User::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->when(array_key_exists($role, User::ROLES), fn ($q) => $q->where('role', $role))
            ->orderBy('name')
            ->paginate(self::ITEMS_PER_PAGE)
            ->withQueryString();

        $counts = User::selectRaw('role, COUNT(*) as total')->groupBy('role')->pluck('total', 'role');

        return view('pengguna.index', [
            'users' => $users,
            'counts' => $counts,
            'roles' => User::ROLES,
            'filters' => ['search' => $search, 'role' => $role],
        ]);
    }

    public function create(): View
    {
        return view('pengguna.create', ['roles' => User::ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $this->messages());

        User::create($validated);

        return redirect()->route('pengguna.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('pengguna.edit', ['user' => $user, 'roles' => User::ROLES]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ], $this->messages());

        // Cegah pengelola mengunci dirinya sendiri dari menu ini.
        if ($user->is($request->user()) && $validated['role'] !== User::ROLE_PENGELOLA) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri.');
        }

        // Password kosong = tidak diubah.
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('pengguna.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('pengguna.index')
            ->with('success', 'Pengguna "' . $user->name . '" berhasil dihapus.');
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi',
            'name.min' => 'Nama minimal 3 karakter',
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.unique' => 'Email sudah terdaftar',
            'role.required' => 'Role wajib dipilih',
            'role.in' => 'Role hanya boleh pengelola, kasir, atau kurir',
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ];
    }
}
