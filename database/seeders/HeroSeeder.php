<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class HeroSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permission = Permission::firstOrCreate(['name' => 'hero.gestionar', 'guard_name' => 'web']);
        Role::where('name', 'Administrador')->where('guard_name', 'web')->first()?->givePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (HeroSlide::exists()) { return; }
        $path = 'hero/hero-mrbulls.png';
        if (!Storage::disk('public')->exists($path)) {
            Storage::disk('public')->put($path, file_get_contents(public_path('images/hero-mrbulls.png')));
        }
        HeroSlide::create([
            'eyebrow' => 'MR BULLS', 'title' => 'Construye', 'highlight' => 'tu legado.',
            'description' => 'Diseños con carácter para quienes no pasan desapercibidos. Descubre la colección de Mr Bulls y encuentra el estilo que te representa.',
            'image' => $path, 'image_alt' => 'Colección Mr Bulls',
            'primary_label' => 'Ver colección', 'primary_url' => '#productos-destacados',
            'secondary_label' => 'Todos los productos', 'secondary_url' => '/productos',
            'brand_line' => 'ESTILO · CARÁCTER · LEGADO', 'sort_order' => 0, 'is_active' => true,
        ]);
    }
}
