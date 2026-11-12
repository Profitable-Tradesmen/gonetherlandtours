<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AdminUserController extends ResourceController
{
    protected function model(): string
    {
        return AdminUser::class;
    }

    protected function config(): array
    {
        return [
            'title' => 'Admin user', 'plural' => 'Admin users', 'route' => 'admin.users', 'sortable' => false,
            'help' => 'Leave the password blank when editing to keep the current one. Minimum 10 characters.',
            'columns' => ['Name' => 'name', 'Email' => 'email'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:120'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'text', 'rules' => 'required|email|max:190'],
                ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'rules' => 'nullable|string|min:10|max:100'],
            ],
        ];
    }

    protected function extraRules(Model $item): array
    {
        return [
            'email' => ['required', 'email', 'max:190', Rule::unique('admin_users', 'email')->ignore($item->id)],
            'password' => [$item->exists ? 'nullable' : 'required', 'string', 'min:10', 'max:100'],
        ];
    }

    protected function cannotDelete(Model $item): ?string
    {
        if ($item->id === Auth::guard('admin')->id()) {
            return 'You cannot delete your own account.';
        }

        return AdminUser::count() <= 1 ? 'You cannot delete the last admin.' : null;
    }
}
