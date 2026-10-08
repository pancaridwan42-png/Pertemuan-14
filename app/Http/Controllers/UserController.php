<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
        }
        $users = $query->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
           'name' => 'required|string|max:255',
           'email' => 'required|string|email|max:255|unique:users',
           'role' => 'required|in:admin,editor,user',
           'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal berisi 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')->with('success', 'Akun Pengguna berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
    'name' => 'required|string|max:255',
    'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
    'role' => 'required|in:admin,editor,user',
    'password' => 'nullable|string|min:8|confirmed',
], [
    'name.required' => 'Nama lengkap wajib diisi.',
    'email.required' => 'Email wajib diisi.',
    'email.unique' => 'Email ini sudah digunakan pengguna lain.',
    'role.required' => 'Role wajib dipilih.',
    'role.in' => 'Role yang dipilih tidak valid.',
    'password.min' => 'Kata sandi minimal berisi 8 karakter.',
    'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
]);

        $data = [
             'name' => $request->name,
             'email' => $request->email,
             'role' => $request->role,
        ];

        // Ganti password hanya jika diinputkan
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data Pengguna berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        // Cegah agar user yang sedang login tidak menghapus akunnya sendiri
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif login!');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Akun Pengguna berhasil dihapus!');
    }
}
