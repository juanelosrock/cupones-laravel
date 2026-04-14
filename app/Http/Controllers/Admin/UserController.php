<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('roles')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->when($request->role, fn($q) => $q->whereHas('roles', fn($r) => $r->where('name', $request->role)))
            ->paginate(20);

        $roles = Role::all();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'document_type' => 'nullable|in:CC,CE,NIT,PP',
            'document_number' => 'nullable|string|max:30|unique:users',
            'role' => 'required|exists:roles,name',
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::create([
            ...$data,
            'password' => bcrypt($data['password']),
        ]);

        $user->assignRole($data['role']);
        AuditService::log('created', User::class, $user->id, [], $user->toArray());

        return redirect()->route('admin.users.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name'            => 'required|string|max:150',
            'email'           => "required|email|unique:users,email,{$user->id}",
            'phone'           => 'nullable|string|max:20',
            'document_type'   => 'nullable|in:CC,CE,NIT,PP',
            'document_number' => "nullable|string|max:30|unique:users,document_number,{$user->id}",
            'role'            => 'required|exists:roles,name',
            'status'          => 'required|in:active,inactive,blocked',
            'password'        => 'nullable|string|min:8|confirmed',
        ];

        $data = $request->validate($rules);

        $old = $user->toArray();

        $updateData = collect($data)->except(['role', 'password', 'password_confirmation'])->toArray();
        if (!empty($data['password'])) {
            $updateData['password'] = bcrypt($data['password']);
        }

        $user->update($updateData);
        $user->syncRoles([$data['role']]);
        AuditService::log('updated', User::class, $user->id, $old, $user->fresh()->toArray());

        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }
        AuditService::log('deleted', User::class, $user->id, $user->toArray(), []);
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado.');
    }
}