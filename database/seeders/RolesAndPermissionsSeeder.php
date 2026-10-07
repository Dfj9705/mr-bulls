<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        $permissions = [
            // Productos
            'productos.ver',
            'productos.crear',
            'productos.editar',
            'productos.eliminar',

            // Categorías
            'categorias.ver',
            'categorias.crear',
            'categorias.editar',
            'categorias.eliminar',

            // Pedidos
            'pedidos.ver',
            'pedidos.procesar',
            'pedidos.cancelar',

            // Pagos
            'pagos.ver',
            'pagos.gestionar',

            // Clientes
            'clientes.ver',
            'clientes.crear',
            'clientes.editar',
            'clientes.eliminar',

            // Reportes
            'reportes.ver',

            // Configuración
            'configuracion.gestionar',

            'panel.acceder',

            'roles.gestionar',

            'inventario.ver',
            'inventario.ajustar',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => 'Administrador',
            'guard_name' => 'web',
        ]);

        Role::firstOrCreate([
            'name' => 'Cliente',
            'guard_name' => 'web',
        ]);

        $admin->syncPermissions(
            Permission::where('guard_name', 'web')->get()
        );

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();
    }
}