<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['permissions'] = $this->record
            ->permissions()
            ->pluck('permissions.id')
            ->toArray();

        return $data;
    }

    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {
        $permissionIds = $data['permissions'] ?? [];

        unset($data['permissions']);

        $record->update($data);

        if ($record->name !== 'Administrador') {
            $permissions = Permission::query()
                ->where('guard_name', 'web')
                ->whereIn('id', $permissionIds)
                ->get();

            $record->syncPermissions($permissions);
        }

        return $record;
    }
}