<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $permissionIds = $data['permissions'] ?? [];

        unset($data['permissions']);

        $data['guard_name'] = 'web';

        $role = static::getModel()::create($data);

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('id', $permissionIds)
            ->get();

        $role->syncPermissions($permissions);

        return $role;
    }
}