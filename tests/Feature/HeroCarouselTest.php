<?php

namespace Tests\Feature;

use App\Models\{HeroSlide, User};
use App\Filament\Resources\HeroSlideResource;
use App\Filament\Resources\HeroSlideResource\Pages\CreateHeroSlide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class HeroCarouselTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('public');
    }

    private function slide(array $overrides = []): HeroSlide
    {
        return HeroSlide::create(array_merge(['title' => 'Colección principal', 'image' => 'hero/test.png', 'image_alt' => 'Gorras Mr Bulls', 'sort_order' => 0, 'is_active' => true], $overrides));
    }

    public function test_home_shows_only_active_slides_in_order_and_controls_for_multiple_slides(): void
    {
        $this->slide(['title' => 'Segunda colección', 'sort_order' => 2]);
        $this->slide(['title' => 'Primera colección', 'sort_order' => 1]);
        $this->slide(['title' => 'Colección oculta', 'is_active' => false]);
        $this->get('/')->assertOk()->assertSeeInOrder(['Primera colección', 'Segunda colección'])
            ->assertDontSee('Colección oculta')->assertSee('Diapositiva siguiente')->assertSee('heroPause');
    }

    public function test_single_slide_has_no_controls_and_all_inactive_hides_hero(): void
    {
        $slide = $this->slide();
        $this->get('/')->assertOk()->assertSee('Colección principal')->assertDontSee('id="heroPause"', false);
        $slide->update(['is_active' => false]);
        $this->get('/')->assertOk()->assertDontSee('id="homeHero"', false);
    }

    public function test_seed_preserves_original_content_without_duplicates(): void
    {
        $this->seed(\Database\Seeders\HeroSeeder::class);
        $slide = HeroSlide::firstOrFail();
        Storage::disk('public')->assertExists($slide->image);
        $this->assertSame('Construye', $slide->title);
        $slide->update(['title' => 'Título editado']);
        $this->seed(\Database\Seeders\HeroSeeder::class);
        $this->assertSame(1, HeroSlide::count());
        $this->assertSame('Título editado', $slide->fresh()->title);
    }

    public function test_admin_can_upload_and_create_slide_and_customer_cannot_manage_it(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));
        $permission = \Spatie\Permission\Models\Permission::create(['name' => 'hero.gestionar', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->givePermissionTo($permission);
        $this->actingAs($admin);
        Livewire::test(CreateHeroSlide::class)->fillForm([
            'title' => 'Nueva temporada', 'image_alt' => 'Colección nueva',
            'image' => UploadedFile::fake()->image('hero.jpg', 1200, 900),
            'primary_label' => 'Comprar', 'primary_url' => '/productos',
            'is_active' => true, 'sort_order' => 3,
        ])->call('create')->assertHasNoFormErrors();
        $slide = HeroSlide::where('title', 'Nueva temporada')->firstOrFail();
        Storage::disk('public')->assertExists($slide->image);
        $this->actingAs(User::factory()->create());
        $this->assertFalse(HeroSlideResource::canViewAny());
        $this->assertFalse(HeroSlideResource::canCreate());
        $this->assertFalse(HeroSlideResource::canEdit($slide));
        $this->assertFalse(HeroSlideResource::canDelete($slide));
        $this->assertFalse(HeroSlideResource::canReorder());
    }

    public function test_links_reject_scripts_protocol_relative_urls_and_missing_labels(): void
    {
        foreach (['javascript:alert(1)', '//example.com', '/\\example.com', "https://example.com\n"] as $url) {
            $this->assertFalse(HeroSlide::validLink($url));
        }
        $this->assertTrue(HeroSlide::validLink('#productos-destacados'));
        $this->assertTrue(HeroSlide::validLink('/productos'));
        $this->assertTrue(HeroSlide::validLink('https://example.com/coleccion'));
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->slide(['primary_url' => '/productos', 'primary_label' => null]);
    }
}
