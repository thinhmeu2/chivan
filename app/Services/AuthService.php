<?php

namespace App\Services;

use App\Enums\ModelEnum;
use App\Enums\PermissionEnum;
use App\Models\Admin;
use App\Models\BaseModel;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use function request;

class AuthService
{
    private Guard $auth;
    protected ?Admin $user = null;

    public function __construct(string $guard = 'admin')
    {
        $this->auth = Auth::guard($guard);
    }

    public function login(array $credentials, bool $remember = false): bool
    {
        $credentials['is_active'] = 1;
        if ($bool = $this->auth->attempt($credentials, $remember)) {
            session(['admin_id' => $this->auth->id()]);
            request()->session()->regenerate();
            $this->user()->last_login = now();
            $this->user()->save();
        }
        return $bool;
    }

    public function logout(): void
    {
        $this->auth->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    public function user()
    {
        if (! $this->user){
            if (app()->runningInConsole())
                $this->user = $this->fakeConsoleUser();
            else
                $this->user = $this->auth->user();
        }
        if ($this->user && empty($this->user->is_admin)){
            /** @var Admin $user */
            $this->user->loadMissing('role.permissions');
        }
        return $this->user;
    }

    public function check(): bool
    {
        return $this->id() > 0;
    }

    public function id(): int
    {
        return $this->user()?->id ?? 0;
    }

    public function hasPermission(string|BaseModel $modelClassName, PermissionEnum $permission): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if ($user->is_admin) {
            return true;
        }

        if (empty($user->role)) {
            return false;
        }

        $modelClassName = is_string($modelClassName)
            ? $modelClassName
            : $modelClassName::class;

        return $user->role->permissions
            ->where('model_type', $modelClassName)
            ->where('type', $permission)
            ->isNotEmpty();
    }

    public function checkPermissionLivewire(string $classNameLivewire, PermissionEnum $permission): void
    {
        $modelClassName = ModelEnum::fromLivewire($classNameLivewire)->value;
        $this->hasPermission($modelClassName, $permission);
    }
    public function checkPermissionModel(string|BaseModel $classNameModel, PermissionEnum $permission): void
    {
        if (! $this->hasPermission(is_string($classNameModel) ? $classNameModel : $classNameModel::class, $permission))
            abort(403, 'Đừng táy máy');
    }

    protected function fakeConsoleUser(): Admin
    {
        $user = new Admin();

        $user->id = 0; // hoặc -1, miễn không trùng DB
        $user->is_admin = true; // console = toàn quyền
        $user->exists = false; // quan trọng, tránh save nhầm

        return $user;
    }
}
