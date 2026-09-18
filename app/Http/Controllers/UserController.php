<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Pengaman: Hanya admin yang boleh akses controller ini
    private function checkAdmin()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Akses ditolak! Halaman ini khusus Administrator.');
        }
    }

    // 1. Menampilkan Halaman Kelola User
    public function index()
    {
        $this->checkAdmin();
        $users = User::orderBy('created_at', 'desc')->get();
        return view('pages.users', compact('users'));
    }

    // 2. Menyimpan Akun Baru
    public function store(Request $request)
    {
        $this->checkAdmin();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            // 'role' dihapus karena sudah otomatis jadi 'cs'
        ], [
            'email.unique' => 'Email ini sudah terdaftar di sistem.',
            'password.min' => 'Password minimal harus 6 karakter.'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'cs', // Dibuat otomatis jadi CS
        ]);

        return redirect()->back()->with('success', 'Akun Petugas CS baru berhasil ditambahkan.');
    }

    // 3. Mengubah Role (Admin <-> CS)
    public function updateRole(Request $request, $id)
    {
        $this->checkAdmin();

        $user = User::findOrFail($id);

        // Mencegah admin menurunkan rolenya sendiri menjadi CS
        if ($user->id === Auth::id() && $request->role !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak dapat mengubah role akun Anda sendiri menjadi CS.');
        }

        $user->role = $request->role;
        $user->save();

        return redirect()->back()->with('success', 'Role pengguna ' . $user->name . ' berhasil diubah menjadi ' . strtoupper($request->role) . '.');
    }

    // 4. Menghapus Akun
    public function destroy($id)
    {
        $this->checkAdmin();

        $user = User::findOrFail($id);

        // Mencegah menghapus akun sendiri yang sedang aktif
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun yang sedang Anda gunakan saat ini.');
        }

        $user->delete();
        return redirect()->back()->with('success', 'Akun pengguna berhasil dihapus.');
    }
}