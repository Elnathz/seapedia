# Banner System + Adaptive Image Crop — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add admin-managed storefront banners (megamart-style big-center carousel + small side banners on `/catalog`) and an adaptive image cropper (1:1 products, 2.5:1 main banner, 3:2 side banner) reused by the product and banner upload forms.

**Architecture:** A `banners` table (placement `main`|`side`) behind `BannerService`; thin `Admin\BannerController` CRUD; Vue `BannerCarousel` + `BannerImage` rendered on the catalog index from a `banners` prop; a reusable `ImageCropField.vue` (wraps `vue-advanced-cropper`) that crops to a configurable aspect ratio and injects the cropped `File` into the existing Inertia `<Form>` via `DataTransfer`.

**Tech Stack:** Laravel 13 (PHP 8.3), Inertia, Vue 3 + TS, shadcn-vue, Tailwind 4, Pest, `vue-advanced-cropper`.

## Global Constraints

- Money is integer IDR; time via `ClockService::now()`; never `v-html`; Eloquent only (no raw SQL).
- Controllers thin → one Service method; every write has a FormRequest; admin routes behind `is_admin` middleware.
- Models use `#[Fillable([...])]` attribute + `casts()` method (match existing `app/Models/*`).
- UI strings via vue-i18n in `resources/js/i18n/{id,en}.ts` (NOT lang JSON). Toasts via `Inertia::flash('toast', ['type'=>..,'message'=>..])`.
- Icons from `@lucide/vue` only (no emoji). Required fields use `<RequiredMark />` + `<InputError />`.
- Image uploads validated server-side: `image|mimes:jpg,jpeg,png,webp|max:2048`.
- Run via Sail: `./vendor/bin/sail ...`. Format before commit: `sail pint` + `sail npm run lint`. Tests: `sail artisan test`.
- Do NOT name megamart or any other e-commerce reference in committed files.
- Banners are a bonus UI enhancement; they must not alter core business rules.

## File Structure

- `database/migrations/2026_06_28_010000_create_banners_table.php` — schema.
- `app/Models/Banner.php` — model.
- `database/factories/BannerFactory.php` — factory.
- `app/Services/BannerService.php` — queries + CRUD + image handling.
- `app/Http/Requests/StoreBannerRequest.php`, `UpdateBannerRequest.php` — validation.
- `app/Http/Controllers/Web/Admin/BannerController.php` — admin CRUD.
- `database/seeders/BannerSeeder.php` — seed 3 main + 4 side.
- `resources/js/components/ImageCropField.vue` — reusable cropper field.
- `resources/js/components/storefront/BannerImage.vue`, `BannerCarousel.vue` — display.
- `resources/js/pages/admin/banners/Index.vue` — admin UI.
- Modify: `routes/web.php`, `app/Http/Controllers/Web/CatalogController.php`, `resources/js/pages/catalog/Index.vue`, `resources/js/pages/seller/products/Form.vue`, `resources/js/components/AppSidebar.vue`, `resources/js/i18n/{id,en}.ts`, `database/seeders/DatabaseSeeder.php`.

---

### Task 1: Banners migration + model + factory

**Files:**
- Create: `database/migrations/2026_06_28_010000_create_banners_table.php`
- Create: `app/Models/Banner.php`
- Create: `database/factories/BannerFactory.php`

**Interfaces:**
- Produces: `App\Models\Banner` with `$placement` (`'main'|'side'`), `image_path`, `title`, `subtitle`, `badge_label`, `cta_label`, `cta_url`, `sort_order`, `is_active`; scope `active()`.

- [ ] **Step 1: Write the migration**

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('placement', 10)->default('main'); // 'main' | 'side'
            $table->string('image_path');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('badge_label')->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['placement', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
```

- [ ] **Step 2: Write the model**

```php
<?php
namespace App\Models;

use Database\Factories\BannerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $placement
 * @property string $image_path
 * @property string $title
 * @property string|null $subtitle
 * @property string|null $badge_label
 * @property string|null $cta_label
 * @property string|null $cta_url
 * @property int $sort_order
 * @property bool $is_active
 */
#[Fillable(['placement', 'image_path', 'title', 'subtitle', 'badge_label', 'cta_label', 'cta_url', 'sort_order', 'is_active'])]
class Banner extends Model
{
    /** @use HasFactory<BannerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    /** @param Builder<Banner> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
```

- [ ] **Step 3: Write the factory**

```php
<?php
namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Banner> */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'placement' => 'main',
            'image_path' => 'banners/placeholder.webp',
            'title' => $this->faker->sentence(3),
            'subtitle' => null,
            'badge_label' => null,
            'cta_label' => null,
            'cta_url' => '/catalog',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }

    public function side(): static
    {
        return $this->state(fn () => ['placement' => 'side']);
    }
}
```

- [ ] **Step 4: Run migration to verify**

Run: `./vendor/bin/sail artisan migrate`
Expected: `... create_banners_table ... DONE`

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_06_28_010000_create_banners_table.php app/Models/Banner.php database/factories/BannerFactory.php
git commit -m "feat(catalog): add banners table, model, and factory"
```

---

### Task 2: BannerService

**Files:**
- Create: `app/Services/BannerService.php`
- Test: `tests/Feature/Catalog/BannerServiceTest.php`

**Interfaces:**
- Consumes: `App\Models\Banner` (Task 1).
- Produces: `BannerService::mainActive(): Collection`, `sideActive(): Collection`, `forStorefront(): array{main: array, side: array}`, `listForAdmin(): Collection`, `create(array $data, ?UploadedFile $image): Banner`, `update(Banner, array, ?UploadedFile): Banner`, `delete(Banner): void`.

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Feature\Catalog;

use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_for_storefront_returns_active_main_and_capped_side(): void
    {
        Banner::factory()->create(['placement' => 'main', 'sort_order' => 1]);
        Banner::factory()->create(['placement' => 'main', 'is_active' => false]); // excluded
        Banner::factory()->count(5)->side()->create(); // 5 active sides

        $data = app(BannerService::class)->forStorefront();

        $this->assertCount(1, $data['main']);
        $this->assertCount(4, $data['side']); // capped at 4
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/sail artisan test --filter BannerServiceTest`
Expected: FAIL (`Class "App\Services\BannerService" not found`)

- [ ] **Step 3: Write the service**

```php
<?php
namespace App\Services;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BannerService
{
    private const IMAGE_DIRECTORY = 'banners';

    /** @return Collection<int, Banner> */
    public function mainActive(): Collection
    {
        return Banner::query()->active()->where('placement', 'main')->orderBy('sort_order')->get();
    }

    /** @return Collection<int, Banner> */
    public function sideActive(): Collection
    {
        return Banner::query()->active()->where('placement', 'side')->orderBy('sort_order')->limit(4)->get();
    }

    /** @return array{main: Collection<int, Banner>, side: Collection<int, Banner>} */
    public function forStorefront(): array
    {
        return ['main' => $this->mainActive(), 'side' => $this->sideActive()];
    }

    /** @return Collection<int, Banner> */
    public function listForAdmin(): Collection
    {
        return Banner::query()->orderBy('placement')->orderBy('sort_order')->get();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, ?UploadedFile $image): Banner
    {
        $data['image_path'] = $image ? $image->store(self::IMAGE_DIRECTORY, 'public') : ($data['image_path'] ?? '');

        return Banner::create($data);
    }

    /** @param array<string, mixed> $data */
    public function update(Banner $banner, array $data, ?UploadedFile $image): Banner
    {
        if ($image) {
            $old = $banner->image_path;
            $data['image_path'] = $image->store(self::IMAGE_DIRECTORY, 'public');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
        }
        $banner->update($data);

        return $banner->refresh();
    }

    public function delete(Banner $banner): void
    {
        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }
        $banner->delete();
    }
}
```

- [ ] **Step 4: Run test to verify it passes**

Run: `./vendor/bin/sail artisan test --filter BannerServiceTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Services/BannerService.php tests/Feature/Catalog/BannerServiceTest.php
git commit -m "feat(catalog): add BannerService for storefront and admin"
```

---

### Task 3: Banner FormRequests

**Files:**
- Create: `app/Http/Requests/StoreBannerRequest.php`
- Create: `app/Http/Requests/UpdateBannerRequest.php`

**Interfaces:**
- Produces: validated keys `placement, title, subtitle, badge_label, cta_label, cta_url, sort_order, is_active, image`.

- [ ] **Step 1: Write StoreBannerRequest**

```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'placement' => ['required', 'in:main,side'],
            'title' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:200'],
            'badge_label' => ['nullable', 'string', 'max:40'],
            'cta_label' => ['nullable', 'string', 'max:40'],
            'cta_url' => ['nullable', 'string', 'max:200'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['image.required' => 'Gambar banner wajib diunggah.', 'image.max' => 'Ukuran gambar maksimal 2MB.'];
    }
}
```

- [ ] **Step 2: Write UpdateBannerRequest**

Same as `StoreBannerRequest` but the image rule is optional (image only replaced when re-uploaded):

```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'placement' => ['required', 'in:main,side'],
            'title' => ['required', 'string', 'max:120'],
            'subtitle' => ['nullable', 'string', 'max:200'],
            'badge_label' => ['nullable', 'string', 'max:40'],
            'cta_label' => ['nullable', 'string', 'max:40'],
            'cta_url' => ['nullable', 'string', 'max:200'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:1000'],
            'is_active' => ['sometimes', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['image.max' => 'Ukuran gambar maksimal 2MB.'];
    }
}
```

- [ ] **Step 3: Commit**

```bash
git add app/Http/Requests/StoreBannerRequest.php app/Http/Requests/UpdateBannerRequest.php
git commit -m "feat(catalog): add banner form requests"
```

---

### Task 4: Admin BannerController + routes

**Files:**
- Create: `app/Http/Controllers/Web/Admin/BannerController.php`
- Modify: `routes/web.php` (admin group + import)
- Test: `tests/Feature/Admin/AdminBannerManagementTest.php`

**Interfaces:**
- Consumes: `BannerService` (Task 2), `Store/UpdateBannerRequest` (Task 3).
- Produces: routes `admin.banners.{index,store,update,destroy}`.

- [ ] **Step 1: Write the failing test**

```php
<?php
namespace Tests\Feature\Admin;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminBannerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_banner_with_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.banners.store'), [
            'placement' => 'main',
            'title' => 'Promo Spesial',
            'cta_url' => '/catalog',
            'image' => UploadedFile::fake()->image('b.jpg', 1200, 480),
        ]);

        $response->assertRedirect(route('admin.banners.index'));
        $this->assertDatabaseHas('banners', ['title' => 'Promo Spesial', 'placement' => 'main']);
    }

    public function test_non_admin_cannot_create_a_banner(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->post(route('admin.banners.store'), ['placement' => 'main', 'title' => 'X'])->assertForbidden();
    }

    public function test_admin_can_delete_a_banner(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $banner = Banner::factory()->create();
        $this->actingAs($admin)->delete(route('admin.banners.destroy', $banner))->assertRedirect();
        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `./vendor/bin/sail artisan test --filter AdminBannerManagementTest`
Expected: FAIL (route not defined)

- [ ] **Step 3: Write the controller**

```php
<?php
namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    public function __construct(private readonly BannerService $banners) {}

    public function index(): Response
    {
        return Inertia::render('admin/banners/Index', ['banners' => $this->banners->listForAdmin()]);
    }

    public function store(StoreBannerRequest $request): RedirectResponse
    {
        $this->banners->create($request->safe()->except('image'), $request->file('image'));
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Banner created.')]);

        return to_route('admin.banners.index');
    }

    public function update(UpdateBannerRequest $request, Banner $banner): RedirectResponse
    {
        $this->banners->update($banner, $request->safe()->except('image'), $request->file('image'));
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Banner updated.')]);

        return to_route('admin.banners.index');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $this->banners->delete($banner);
        Inertia::flash('toast', ['type' => 'success', 'message' => __('Banner deleted.')]);

        return to_route('admin.banners.index');
    }
}
```

- [ ] **Step 4: Add routes** (in the `is_admin` group of `routes/web.php`, and import the controller at top as `use App\Http\Controllers\Web\Admin\BannerController as AdminBannerController;`)

```php
Route::get('banners', [AdminBannerController::class, 'index'])->name('banners.index');
Route::post('banners', [AdminBannerController::class, 'store'])->name('banners.store');
Route::put('banners/{banner}', [AdminBannerController::class, 'update'])->name('banners.update');
Route::delete('banners/{banner}', [AdminBannerController::class, 'destroy'])->name('banners.destroy');
```

- [ ] **Step 5: Run test to verify it passes**

Run: `./vendor/bin/sail artisan test --filter AdminBannerManagementTest`
Expected: PASS (3 tests)

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Web/Admin/BannerController.php routes/web.php tests/Feature/Admin/AdminBannerManagementTest.php
git commit -m "feat(admin): add banner management CRUD"
```

---

### Task 5: CatalogController banners prop

**Files:**
- Modify: `app/Http/Controllers/Web/CatalogController.php`
- Test: extend `tests/Feature/Catalog/CatalogTest.php`

**Interfaces:**
- Consumes: `BannerService::forStorefront()` (Task 2).
- Produces: Inertia prop `banners` on `catalog/Index`.

- [ ] **Step 1: Write the failing test** (add to `CatalogTest`)

```php
public function test_catalog_includes_active_banners(): void
{
    \App\Models\Banner::factory()->create(['placement' => 'main']);

    $this->get(route('catalog.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('banners.main', 1));
}
```

- [ ] **Step 2: Run to verify fail**

Run: `./vendor/bin/sail artisan test --filter test_catalog_includes_active_banners`
Expected: FAIL (`banners` prop missing)

- [ ] **Step 3: Inject `BannerService` and pass the prop**

In `CatalogController`, add `private readonly BannerService $banners` to the constructor (alongside the existing `$catalog`, `$categories`), then add to the `Inertia::render('catalog/Index', [...])` array:

```php
'banners' => $this->banners->forStorefront(),
```

Add `use App\Services\BannerService;` at the top.

- [ ] **Step 4: Run to verify pass**

Run: `./vendor/bin/sail artisan test --filter CatalogTest`
Expected: PASS

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/Web/CatalogController.php tests/Feature/Catalog/CatalogTest.php
git commit -m "feat(catalog): expose storefront banners to the catalog page"
```

---

### Task 6: BannerSeeder

**Files:**
- Create: `database/seeders/BannerSeeder.php`
- Modify: `database/seeders/DatabaseSeeder.php`

**Interfaces:**
- Consumes: `Banner` model. Uses placeholder image paths from `public/images/banners/` (assets generated separately per `implementation_plan.md` §4.4).

- [ ] **Step 1: Write the seeder** (generates a solid-color placeholder webp if the asset file is absent, mirroring `StoreProductSeeder::generatePlaceholderImage`)

```php
<?php
namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $main = [
            ['title' => 'Gratis ongkir pesanan pertama', 'badge_label' => 'Promo', 'cta_label' => 'Belanja sekarang', 'cta_url' => '/catalog'],
            ['title' => 'Diskon kuliner kampus', 'cta_label' => 'Lihat menu', 'cta_url' => '/catalog?category=makanan'],
            ['title' => 'Jadi kurir kampus', 'cta_label' => 'Daftar kurir', 'cta_url' => '/register'],
        ];
        foreach ($main as $i => $b) {
            Banner::create([...$b, 'placement' => 'main', 'image_path' => "images/banners/banner-main-".($i + 1).".webp", 'sort_order' => $i, 'is_active' => true]);
        }

        $side = ['Elektronik' => 'elektronik', 'Fashion' => 'fashion', 'Minuman' => 'minuman', 'Kebutuhan Harian' => 'kebutuhan-harian'];
        $i = 0;
        foreach ($side as $title => $slug) {
            Banner::create(['placement' => 'side', 'title' => $title, 'cta_url' => "/catalog?category={$slug}", 'image_path' => "images/banners/banner-side-".(++$i).".webp", 'sort_order' => $i, 'is_active' => true]);
        }
    }
}
```

> Note: `image_path` here points under `public/images/banners/`. The display components (Task 9) must resolve `image_path` that already starts with `images/` as `/{path}` (public) and otherwise as `/storage/{path}` (uploaded). Keep that rule consistent.

- [ ] **Step 2: Register in `DatabaseSeeder::run()`** after `CategorySeeder`:

```php
$this->call(BannerSeeder::class);
```

- [ ] **Step 3: Run seed to verify**

Run: `./vendor/bin/sail artisan migrate:fresh --seed`
Expected: `Database\Seeders\BannerSeeder ... DONE`; 3 main + 4 side rows.

- [ ] **Step 4: Commit**

```bash
git add database/seeders/BannerSeeder.php database/seeders/DatabaseSeeder.php
git commit -m "feat(db): seed storefront banners"
```

---

### Task 7: Install cropper + ImageCropField.vue

**Files:**
- Modify: `package.json` (add dep)
- Create: `resources/js/components/ImageCropField.vue`
- Modify: `resources/js/i18n/{id,en}.ts` (keys `product.cropApply`, `product.cropChoose`)

**Interfaces:**
- Produces: `<ImageCropField :aspect-ratio="number" name="image" :max-bytes="2097152" />` — renders a file picker + cropper dialog; on apply, injects the cropped `File` into a hidden `<input type="file" :name="name">` via `DataTransfer` so the surrounding Inertia `<Form>` submits it; emits `change` with a preview URL.

- [ ] **Step 1: Install the dependency**

Run: `./vendor/bin/sail npm i vue-advanced-cropper`
Expected: added to `package.json` dependencies.

- [ ] **Step 2: Add i18n keys** (in the `product` block of both `id.ts` and `en.ts`)

```ts
// id.ts
cropChoose: 'Pilih gambar',
cropApply: 'Terapkan',
// en.ts
cropChoose: 'Choose image',
cropApply: 'Apply',
```

- [ ] **Step 3: Write the component**

```vue
<script setup lang="ts">
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const props = withDefaults(
    defineProps<{
        aspectRatio: number;
        name?: string;
        maxBytes?: number;
        initialUrl?: string | null;
    }>(),
    { name: 'image', maxBytes: 2 * 1024 * 1024, initialUrl: null },
);

const { t } = useI18n();
const fileInput = ref<HTMLInputElement | null>(null); // hidden input the Form submits
const cropOpen = ref(false);
const cropSrc = ref<string | null>(null);
const previewUrl = ref<string | null>(props.initialUrl);
const cropperRef = ref<InstanceType<typeof Cropper> | null>(null);

function onPick(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    if (file.size > props.maxBytes) {
        toast.error(t('product.imageTooLarge'));
        return;
    }
    cropSrc.value = URL.createObjectURL(file);
    cropOpen.value = true;
}

function apply() {
    const result = cropperRef.value?.getResult();
    const canvas = result?.canvas;
    if (!canvas) return;
    canvas.toBlob((blob) => {
        if (!blob) return;
        const cropped = new File([blob], 'image.jpg', { type: 'image/jpeg' });
        const dt = new DataTransfer();
        dt.items.add(cropped);
        if (fileInput.value) fileInput.value.files = dt.files; // Inertia <Form> picks this up by name
        previewUrl.value = URL.createObjectURL(cropped);
        cropOpen.value = false;
    }, 'image/jpeg', 0.9);
}
</script>

<template>
    <div class="grid gap-2">
        <input ref="fileInput" type="file" :name="name" class="hidden" />
        <label
            class="inline-flex w-fit cursor-pointer items-center rounded-md border border-input bg-background px-3 py-2 text-sm hover:bg-muted"
        >
            {{ t('product.cropChoose') }}
            <input
                type="file"
                accept="image/png,image/jpeg,image/webp"
                class="hidden"
                @change="onPick"
            />
        </label>
        <img
            v-if="previewUrl"
            :src="previewUrl"
            alt=""
            class="mt-1 size-32 rounded-md border border-border object-cover"
        />

        <Dialog v-model:open="cropOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ t('product.cropChoose') }}</DialogTitle>
                </DialogHeader>
                <Cropper
                    v-if="cropSrc"
                    ref="cropperRef"
                    :src="cropSrc"
                    :stencil-props="{ aspectRatio }"
                    class="h-72 bg-muted"
                />
                <DialogFooter>
                    <Button type="button" @click="apply">{{ t('product.cropApply') }}</Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
```

- [ ] **Step 4: Type-check + lint**

Run: `./vendor/bin/sail npm run types:check && ./vendor/bin/sail npx eslint resources/js/components/ImageCropField.vue`
Expected: no errors.

- [ ] **Step 5: Commit**

```bash
git add package.json package-lock.json resources/js/components/ImageCropField.vue resources/js/i18n/id.ts resources/js/i18n/en.ts
git commit -m "feat(product): add reusable adaptive image cropper field"
```

---

### Task 8: Use the cropper in the product form

**Files:**
- Modify: `resources/js/pages/seller/products/Form.vue`

**Interfaces:**
- Consumes: `ImageCropField` (Task 7). Products use `:aspect-ratio="1"`.

- [ ] **Step 1: Replace the image input**

In `Form.vue`, replace the existing `<Input id="image" name="image" type="file" ... @change="onImageChange" />` block (and its preview `<img>`) with:

```vue
<div class="grid gap-2">
    <Label>{{ t('product.imageLabel') }}</Label>
    <ImageCropField
        :aspect-ratio="1"
        name="image"
        :initial-url="product?.image_path ? `/storage/${product.image_path}` : null"
    />
    <p class="text-xs text-muted-foreground">{{ t('product.imageHelp') }}</p>
    <InputError :message="errors.image" />
</div>
```

Add `import ImageCropField from '@/components/ImageCropField.vue';`. Remove the now-unused `onImageChange`, `previewUrl`, `resetPreview`, and the old `MAX_IMAGE_BYTES` if no longer referenced.

- [ ] **Step 2: Manual verify**

Run: `./vendor/bin/sail npm run build`, then create/edit a product: pick an image → crop dialog opens locked to square → Apply → preview shows → submit saves the cropped square image.

- [ ] **Step 3: Run product tests** (ensure server still accepts the upload)

Run: `./vendor/bin/sail artisan test --filter ProductManagementTest`
Expected: PASS

- [ ] **Step 4: Commit**

```bash
git add resources/js/pages/seller/products/Form.vue
git commit -m "feat(product): crop product images to 1:1 on upload"
```

---

### Task 9: BannerImage + BannerCarousel components

**Files:**
- Create: `resources/js/components/storefront/BannerImage.vue`
- Create: `resources/js/components/storefront/BannerCarousel.vue`
- Create: `resources/js/types/banner.ts`

**Interfaces:**
- Produces: `BannerNode` type; `<BannerImage :banner="b" />`, `<BannerCarousel :slides="main" />`. Both resolve `image_path`: starts with `images/` → `/{path}`; else → `/storage/{path}`.

- [ ] **Step 1: Type**

```ts
// resources/js/types/banner.ts
export interface BannerNode {
    id: number;
    placement: 'main' | 'side';
    image_path: string;
    title: string;
    subtitle: string | null;
    badge_label: string | null;
    cta_label: string | null;
    cta_url: string | null;
}
export function bannerSrc(path: string): string {
    return path.startsWith('images/') ? `/${path}` : `/storage/${path}`;
}
```

- [ ] **Step 2: BannerImage.vue**

```vue
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { bannerSrc, type BannerNode } from '@/types/banner';

defineProps<{ banner: BannerNode }>();
</script>

<template>
    <Link
        :href="banner.cta_url ?? '/catalog'"
        class="group relative block overflow-hidden rounded-xl border border-border"
    >
        <img
            :src="bannerSrc(banner.image_path)"
            :alt="banner.title"
            loading="lazy"
            class="aspect-[3/2] w-full object-cover transition-transform duration-300 group-hover:scale-105"
        />
        <span
            v-if="banner.badge_label"
            class="absolute top-2 left-2 rounded-full bg-primary px-2 py-0.5 text-xs font-medium text-primary-foreground"
        >
            {{ banner.badge_label }}
        </span>
    </Link>
</template>
```

- [ ] **Step 3: BannerCarousel.vue** (auto-rotate, reduced-motion aware, transform-only)

```vue
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { onMounted, onUnmounted, ref } from 'vue';
import { bannerSrc, type BannerNode } from '@/types/banner';

const props = defineProps<{ slides: BannerNode[] }>();
const active = ref(0);
let timer: ReturnType<typeof setInterval> | null = null;

function go(i: number) {
    active.value = (i + props.slides.length) % props.slides.length;
}

onMounted(() => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    if (props.slides.length > 1) timer = setInterval(() => go(active.value + 1), 5000);
});
onUnmounted(() => timer && clearInterval(timer));
</script>

<template>
    <div class="relative overflow-hidden rounded-xl border border-border">
        <div
            class="flex transition-transform duration-500 ease-out motion-reduce:transition-none"
            :style="{ transform: `translateX(-${active * 100}%)` }"
        >
            <Link
                v-for="slide in slides"
                :key="slide.id"
                :href="slide.cta_url ?? '/catalog'"
                class="block w-full shrink-0"
            >
                <img
                    :src="bannerSrc(slide.image_path)"
                    :alt="slide.title"
                    class="aspect-[5/2] w-full object-cover"
                />
            </Link>
        </div>
        <template v-if="slides.length > 1">
            <button
                type="button"
                class="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-white/80 p-1.5 shadow hover:bg-white"
                aria-label="Sebelumnya"
                @click="go(active - 1)"
            >
                <ChevronLeft class="size-4" />
            </button>
            <button
                type="button"
                class="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-white/80 p-1.5 shadow hover:bg-white"
                aria-label="Berikutnya"
                @click="go(active + 1)"
            >
                <ChevronRight class="size-4" />
            </button>
            <div class="absolute bottom-2 left-1/2 flex -translate-x-1/2 gap-1.5">
                <button
                    v-for="(s, i) in slides"
                    :key="s.id"
                    type="button"
                    class="size-2 rounded-full"
                    :class="i === active ? 'bg-primary' : 'bg-white/70'"
                    :aria-label="`Slide ${i + 1}`"
                    @click="go(i)"
                />
            </div>
        </template>
    </div>
</template>
```

- [ ] **Step 4: Type-check + lint**

Run: `./vendor/bin/sail npm run types:check && ./vendor/bin/sail npx eslint resources/js/components/storefront/*.vue`
Expected: no errors.

- [ ] **Step 5: Commit**

```bash
git add resources/js/components/storefront/ resources/js/types/banner.ts
git commit -m "feat(catalog): add banner image and carousel components"
```

---

### Task 10: Catalog index banner layout

**Files:**
- Modify: `resources/js/pages/catalog/Index.vue`

**Interfaces:**
- Consumes: `banners` prop (Task 5), `BannerCarousel`/`BannerImage` (Task 9).

- [ ] **Step 1: Add the prop + imports**

In `<script setup>`, import the components and `BannerNode`, and extend props:

```ts
import BannerCarousel from '@/components/storefront/BannerCarousel.vue';
import BannerImage from '@/components/storefront/BannerImage.vue';
import type { BannerNode } from '@/types/banner';
// add to defineProps generic:
banners: { main: BannerNode[]; side: BannerNode[] };
```

- [ ] **Step 2: Replace the text hero** with the banner block (keep the existing search + category chips below it). Replace the hero `<div class="overflow-hidden rounded-2xl bg-gradient-to-br ...">...</div>` with:

```vue
<div v-if="banners.main.length || banners.side.length">
    <!-- Desktop: 2 side | carousel | 2 side -->
    <div class="hidden gap-3 lg:grid lg:grid-cols-4">
        <div class="flex flex-col gap-3">
            <BannerImage v-for="b in banners.side.slice(0, 2)" :key="b.id" :banner="b" />
        </div>
        <div class="col-span-2">
            <BannerCarousel v-if="banners.main.length" :slides="banners.main" />
        </div>
        <div class="flex flex-col gap-3">
            <BannerImage v-for="b in banners.side.slice(2, 4)" :key="b.id" :banner="b" />
        </div>
    </div>
    <!-- Mobile/tablet: carousel then side grid -->
    <div class="space-y-3 lg:hidden">
        <BannerCarousel v-if="banners.main.length" :slides="banners.main" />
        <div v-if="banners.side.length" class="grid grid-cols-2 gap-3">
            <BannerImage v-for="b in banners.side" :key="b.id" :banner="b" />
        </div>
    </div>
</div>
<!-- Fallback hero when no banners exist -->
<div v-else class="overflow-hidden rounded-2xl bg-gradient-to-br from-primary/15 via-secondary/40 to-background px-6 py-10 sm:px-10 sm:py-14">
    <h1 class="text-2xl font-semibold sm:text-3xl">{{ t('catalog.heroTitle') }}</h1>
    <p class="mt-2 max-w-md text-sm text-muted-foreground sm:text-base">{{ t('catalog.heroSubtitle') }}</p>
</div>

<!-- Search bar (moved below banners) -->
<div class="mt-6 flex max-w-md items-center gap-2">
    <div class="relative flex-1">
        <Search class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
        <Input v-model="query" :placeholder="t('catalog.searchPlaceholder')" class="pl-9" @keyup.enter="applySearch" @blur="applySearch" />
    </div>
    <Spinner v-if="loading" class="size-4" />
</div>
```

- [ ] **Step 3: Build + manual verify at 375 / 768 / 1280 / 1920**

Run: `./vendor/bin/sail npm run build`
Expected: desktop shows 2 | carousel | 2; mobile shows carousel then 2-col side grid; with no banners, the text hero renders; search + chips appear below.

- [ ] **Step 4: Commit**

```bash
git add resources/js/pages/catalog/Index.vue
git commit -m "feat(catalog): render storefront banners on the catalog page"
```

---

### Task 11: Admin banners page + sidebar + i18n

**Files:**
- Create: `resources/js/pages/admin/banners/Index.vue`
- Modify: `resources/js/components/AppSidebar.vue`, `resources/js/i18n/{id,en}.ts`

**Interfaces:**
- Consumes: `ImageCropField` (Task 7), wayfinder action `@/actions/App/Http/Controllers/Web/Admin/BannerController` (generated on build after Task 4).

- [ ] **Step 1: Add i18n keys** (admin block, both files): `manageBannersTitle`, `addBanner`, `editBanner`, `bannerTitleLabel`, `bannerPlacementLabel`, `bannerPlacementMain`, `bannerPlacementSide`, `bannerCtaUrlLabel`, `bannerBadgeLabel`, `bannersEmptyTitle`, `bannersEmptyDescription`, `deleteBannerConfirmTitle`.

- [ ] **Step 2: Build the admin page** following the exact structure of `resources/js/pages/admin/categories/Index.vue` (Heading + add button, `EmptyState`, grid of preview cards with placement badge + edit/delete, a create/edit `Dialog` whose form is `AdminBannerController.store.form()` / `.update.form(banner.id)`). Inside the dialog form use:
  - `placement` via shadcn `Select` (main/side) bound to a hidden input,
  - `title` Input (`required maxlength=120`) + `RequiredMark`,
  - `cta_url` Input (`maxlength=200`), `badge_label` Input (`maxlength=40`),
  - `<ImageCropField :aspect-ratio="placement === 'main' ? 2.5 : 1.5" name="image" />`,
  - submit `disabled` while processing; delete via a confirm `Dialog` posting `AdminBannerController.destroy.form(banner.id)`.
  Image preview in the card uses `bannerSrc(banner.image_path)` from `@/types/banner`.

- [ ] **Step 3: Add the sidebar menu item** in `AppSidebar.vue`: import `Images` from `@lucide/vue`, import `index as indexAdminBanners from '@/routes/admin/banners'`, and add to the `auth.user?.is_admin` block:

```ts
{ title: t('admin.manageBannersTitle'), href: indexAdminBanners(), icon: Images },
```

- [ ] **Step 4: Build + verify**

Run: `./vendor/bin/sail npm run build && ./vendor/bin/sail npm run types:check && ./vendor/bin/sail npm run lint`
Expected: no errors; admin sees a "Banner" menu; can create a main banner (crop 2.5:1) and a side banner (crop 3:2).

- [ ] **Step 5: Commit** (include the new wayfinder files for the banner controller per the repo's wayfinder policy — see `progress.md` §5)

```bash
git add resources/js/pages/admin/banners/Index.vue resources/js/components/AppSidebar.vue resources/js/i18n/id.ts resources/js/i18n/en.ts
git add -f resources/js/actions/App/Http/Controllers/Web/Admin/BannerController.ts resources/js/routes/admin/banners/index.ts
git add resources/js/actions/App/Http/Controllers/Web/Admin/index.ts resources/js/routes/admin/index.ts
git checkout -- resources/js/actions resources/js/routes resources/js/wayfinder
git commit -m "feat(admin): add banner management page and sidebar entry"
```

---

## Self-Review

- **Spec coverage:** banner schema/admin/display (§4.0) ✓ Tasks 1–6, 9–11; adaptive crop (§4.1) ✓ Tasks 7–8 + 11. Seeders/responsive/images/seller-page are separate plans (out of this plan's scope by design).
- **Placeholder scan:** Task 11 step 2 references the categories page as a structural template but states every field/control explicitly rather than saying "similar to" — acceptable since the controls are enumerated. No TBD/TODO remain.
- **Type consistency:** `bannerSrc()` + `BannerNode` defined in Task 9 and reused in Tasks 10–11; `forStorefront()` shape (`{main, side}`) consistent across Tasks 2, 5, 10; `image` field name consistent across cropper (Task 7), product form (Task 8), banner requests (Task 3).
- **Image path rule:** seeded banners live under `public/images/banners/` (`bannerSrc` → `/images/...`); uploaded banners/products under `storage` (`/storage/...`). Rule stated in Tasks 6 and 9 and applied consistently.
