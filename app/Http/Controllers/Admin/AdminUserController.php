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
            'title' => 'User', 'plural' => 'Admin users', 'route' => 'admin.users', 'sortable' => false,
            'help' => 'Administrators have full access. Editors can only manage Enquiries, Packages, Tour types, Destinations, Pages, FAQs, Testimonials and Media (no Quiz, Settings or Users). Leave the password blank when editing to keep the current one. Minimum 10 characters.',
            'columns' => ['Name' => 'name', 'Email' => 'email', 'Role' => 'role_label'],
            'fields' => [
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'rules' => 'required|string|max:120'],
                ['name' => 'email', 'label' => 'Email', 'type' => 'text', 'rules' => 'required|email|max:190'],
                ['name' => 'role', 'label' => 'Access level', 'type' => 'select', 'options' => AdminUser::ROLES, 'default' => AdminUser::ROLE_EDITOR, 'rules' => 'required'],
                ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'rules' => 'nullable|string|min:10|max:100'],
            ],
        ];
    }

    protected function extraRules(Model $item): array
    {
        return [
            'email' => ['required', 'email', 'max:190', Rule::unique('admin_users', 'email')->ignore($item->id)],
            'password' => [$item->exists ? 'nullable' : 'required', 'string', 'min:10', 'max:100'],
            'role' => ['required', Rule::in(array_keys(AdminUser::ROLES)), function ($attr, $value, $fail) use ($item) {
                // Never lock everyone out: you cannot demote yourself or the last administrator.
                if ($item->exists && $item->isAdmin() && $value !== AdminUser::ROLE_ADMIN
                    && ($item->id === Auth::guard('admin')->id() || AdminUser::where('role', AdminUser::ROLE_ADMIN)->count() <= 1)) {
                    $fail('You cannot remove administrator access from yourself or the last administrator.');
                }
            }],
        ];
    }

    protected function cannotDelete(Model $item): ?string
    {
        if ($item->id === Auth::guard('admin')->id()) {
            return 'You cannot delete your own account.';
        }

        return $item->isAdmin() && AdminUser::where('role', AdminUser::ROLE_ADMIN)->count() <= 1 ? 'You cannot delete the last administrator.' : null;
    }
}
