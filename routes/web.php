<?php

use Contensio\Http\Controllers\Admin\ActivityLogController;
use Contensio\Http\Controllers\Admin\ReviewController;
use Contensio\Http\Controllers\Admin\CommentController;
use Contensio\Http\Controllers\Admin\ContactController;
use Contensio\Http\Controllers\Admin\ContactLabelController;
use Contensio\Http\Controllers\Admin\ContactMessageController;
use Contensio\Http\Controllers\Frontend\CommentSubmitController;
use Contensio\Http\Controllers\Frontend\ContactPageController;
use Contensio\Http\Controllers\Frontend\UserProfileController;
use Contensio\Http\Controllers\Admin\BlockController;
use Contensio\Http\Controllers\Admin\ContentController;
use Contensio\Http\Controllers\Admin\ContentTypeController;
use Contensio\Http\Controllers\Admin\FieldController;
use Contensio\Http\Controllers\Admin\FieldGroupController;
use Contensio\Http\Controllers\Admin\SearchController;
use Contensio\Http\Controllers\Admin\TaxonomyController;
use Contensio\Http\Controllers\Admin\TermController;
use Contensio\Http\Controllers\Admin\DashboardController;
use Contensio\Http\Controllers\Admin\LanguageController;
use Contensio\Http\Controllers\Admin\MediaController;
use Contensio\Http\Controllers\Admin\MenuController;
use Contensio\Http\Controllers\Admin\PluginController;
use Contensio\Http\Controllers\Admin\ProfileController;
use Contensio\Http\Controllers\Admin\RedirectController;
use Contensio\Http\Controllers\Admin\RoleController;
use Contensio\Http\Controllers\Admin\SettingController;
use Contensio\Http\Controllers\Admin\WhitelabelController;
use Contensio\Http\Controllers\Admin\ThemeController;
use Contensio\Http\Controllers\Admin\Tools\BackupController;
use Contensio\Http\Controllers\Admin\WidgetController;
use Contensio\Http\Controllers\Admin\Tools\ImportExportController;
use Contensio\Http\Controllers\Admin\UserController;
use Contensio\Http\Controllers\Api\ContentApiController;
use Contensio\Http\Controllers\Auth\LoginController;
use Contensio\Http\Controllers\Auth\RegisterController;
use Contensio\Http\Controllers\Frontend\FeedController;
use Contensio\Http\Controllers\Frontend\FrontendController;
use Contensio\Http\Controllers\Frontend\FrontendSearchController;
use Contensio\Http\Controllers\Frontend\FrontendTaxonomyController;
use Contensio\Http\Controllers\Frontend\SeoController;
use Illuminate\Support\Facades\Route;

// ─── SEO (sitemap + robots) ────────────────────────────────────────────────
// Registered outside the 'web' group so no session/CSRF overhead for crawlers.
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('contensio.seo.sitemap');
Route::get('/robots.txt',  [SeoController::class, 'robots'])->name('contensio.seo.robots');
Route::get('/feed',        [FeedController::class, 'index'])->name('contensio.feed');

// ─── Public JSON API (read-only, no auth) ──────────────────────────────────
// /api/v1/content/{type}         — paginated list of published entries
// /api/v1/content/{type}/{slug}  — single entry by slug
Route::middleware('web')->prefix('api/v1')->name('contensio.api.')->group(function () {
    Route::get('/content/{type}',        [ContentApiController::class, 'index'])->name('content.index');
    Route::get('/content/{type}/{slug}', [ContentApiController::class, 'show'])->name('content.show');
});

// ─── Auth ──────────────────────────────────────────────────────────────────
// Declared BEFORE the frontend catch-all so /login + /logout win the match race
// even when Laravel's router compiles in registration order.
Route::middleware('web')->group(function () {
    Route::get('/login',    [LoginController::class,  'showLogin'])->name('contensio.login');
    Route::post('/login',   [LoginController::class,  'login'])->name('contensio.login.store');
    Route::post('/logout',  [LoginController::class,  'logout'])->name('contensio.logout')->middleware('auth');
    Route::get('/register', [RegisterController::class, 'showRegister'])->name('contensio.register');
    Route::post('/register',[RegisterController::class, 'register'])->name('contensio.register.store');
});

// ─── Contact page (dynamic slug from settings — registered BEFORE the page catch-all) ──
// Slugs are loaded from the database; the try/catch makes it safe during install
// when the DB may not yet exist.
Route::middleware('web')->group(function () {
    try {
        $contactSlugs = \Contensio\Models\Setting::where('module', 'contact')
            ->where('setting_key', 'slugs')
            ->value('value');
        $slugs = json_decode($contactSlugs ?? '{}', true) ?? [];

        foreach (array_unique(array_values(array_filter($slugs))) as $slug) {
            Route::get('/' . $slug,  [ContactPageController::class, 'show'])->name('contensio.contact');
            Route::post('/' . $slug, [ContactPageController::class, 'submit'])->name('contensio.contact.submit');
        }

        // Fallback slug if none configured
        if (empty($slugs)) {
            Route::get('/contact',  [ContactPageController::class, 'show'])->name('contensio.contact');
            Route::post('/contact', [ContactPageController::class, 'submit'])->name('contensio.contact.submit');
        }
    } catch (\Throwable) {
        // DB not ready yet (fresh install) — skip contact routes
    }
});

// ─── Frontend ──────────────────────────────────────────────────────────────
Route::middleware('web')->group(function () {
    // Comments (public POST endpoint — no auth required)
    Route::post('/comments',         [CommentSubmitController::class, 'store'])->name('contensio.comments.store');
    Route::get('/author/{code}',     [UserProfileController::class, 'show'])->name('contensio.author');

    Route::get('/',              [FrontendController::class, 'home'])->name('contensio.home');
    Route::get('/blog',          [FrontendController::class, 'archive'])->name('contensio.blog');
    Route::get('/blog/{slug}',   [FrontendController::class, 'post'])->name('contensio.post');
    Route::get('/search',        [FrontendSearchController::class, 'index'])->name('contensio.search');

    // Taxonomy term archives: /{taxonomy-slug}/{term-slug}
    // Registered before the page catch-all so two-segment taxonomy URLs take priority.
    // The taxonomySlug constraint must exclude the admin route prefix and other
    // reserved first-path segments so that e.g. /account/pages is not swallowed
    // by this route before the admin group can match it.
    Route::get('/{taxonomySlug}/{termSlug}', [FrontendTaxonomyController::class, 'term'])
        ->where('taxonomySlug', '^(?!login(?:/|$)|logout(?:/|$)|' . config('contensio.route_prefix', 'account') . '(?:/|$)|api(?:/|$)|livewire(?:/|$))[a-z][a-z0-9-]+$')
        ->where('termSlug',     '[a-z][a-z0-9-]+')
        ->name('contensio.taxonomy.term');

    // Catch-all for pages by slug. Constrained to keep framework/admin/auth
    // paths out so they always reach their dedicated routes even if the route
    // cache is rebuilt in a different order. Reserved prefixes: login, logout,
    // account, Fortify auth (forgot-password, reset-password, email, user,
    // two-factor-*), sitemap/robots/up, api, livewire, dev tooling.
    Route::get('/{slug}', [FrontendController::class, 'page'])
        ->where('slug', '^(?!login$|logout$|account(?:/|$)|forgot-password(?:/|$)?$|reset-password(?:/|$)|email(?:/|$)|user(?:/|$)|two-factor(?:-|$)|sitemap\.xml$|robots\.txt$|up$|api(?:/|$)|livewire(?:/|$)|_debugbar(?:/|$)|_ignition(?:/|$))[^.]+$')
        ->name('contensio.page');
});

// ─── Admin panel ───────────────────────────────────────────────────────────
Route::prefix(config('contensio.route_prefix'))
    ->name('contensio.account.')
    ->middleware(['web', 'contensio.auth', 'contensio.admin'])
    ->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Profile (current user's own account + 2FA management)
        Route::get('/profile',              [ProfileController::class, 'index'])->name('profile');
        Route::put('/profile',              [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password',     [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::post('/profile/avatar',      [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
        Route::delete('/profile/avatar',    [ProfileController::class, 'removeAvatar'])->name('profile.avatar.remove');
        Route::delete('/profile',           [ProfileController::class, 'destroy'])->name('profile.destroy');

        // Global admin search
        Route::get('/search', [SearchController::class, 'index'])->name('search');

        // Pages
        Route::get('/pages',             [ContentController::class, 'pages'])->name('pages.index');
        Route::get('/pages/create',      [ContentController::class, 'createPage'])->name('pages.create');
        Route::post('/pages/bulk',       [ContentController::class, 'bulkPages'])->name('pages.bulk');
        Route::post('/pages',            [ContentController::class, 'storePage'])->name('pages.store');
        Route::get('/pages/{id}/edit',   [ContentController::class, 'editPage'])->name('pages.edit');
        Route::put('/pages/{id}',        [ContentController::class, 'updatePage'])->name('pages.update');
        Route::delete('/pages/{id}',     [ContentController::class, 'destroyPage'])->name('pages.destroy');
        Route::post('/pages/{id}/clone', [ContentController::class, 'clonePage'])->name('pages.clone');

        // Posts
        Route::get('/posts',             [ContentController::class, 'posts'])->name('posts.index');
        Route::get('/posts/create',      [ContentController::class, 'createPost'])->name('posts.create');
        Route::post('/posts/bulk',       [ContentController::class, 'bulkPosts'])->name('posts.bulk');
        Route::post('/posts',            [ContentController::class, 'storePost'])->name('posts.store');
        Route::get('/posts/{id}/edit',   [ContentController::class, 'editPost'])->name('posts.edit');
        Route::put('/posts/{id}',        [ContentController::class, 'updatePost'])->name('posts.update');
        Route::delete('/posts/{id}',     [ContentController::class, 'destroyPost'])->name('posts.destroy');
        Route::post('/posts/{id}/clone', [ContentController::class, 'clonePost'])->name('posts.clone');

        // Custom content types (non-system) — type must start with a letter
        Route::get('/content/{type}',                  [ContentController::class, 'indexContent'])->name('content.index')->where('type', '[a-z][a-z0-9_-]*');
        Route::get('/content/{type}/create',           [ContentController::class, 'createContent'])->name('content.create')->where('type', '[a-z][a-z0-9_-]*');
        Route::post('/content/{type}/bulk',            [ContentController::class, 'bulkContent'])->name('content.bulk')->where('type', '[a-z][a-z0-9_-]*');
        Route::post('/content/{type}',                 [ContentController::class, 'storeContent'])->name('content.store')->where('type', '[a-z][a-z0-9_-]*');
        Route::get('/content/{type}/{id}/edit',        [ContentController::class, 'editContent'])->name('content.edit')->where('type', '[a-z][a-z0-9_-]*');
        Route::put('/content/{type}/{id}',             [ContentController::class, 'updateContent'])->name('content.update')->where('type', '[a-z][a-z0-9_-]*');
        Route::delete('/content/{type}/{id}',          [ContentController::class, 'destroyContent'])->name('content.destroy')->where('type', '[a-z][a-z0-9_-]*');
        Route::post('/content/{type}/{id}/clone',      [ContentController::class, 'cloneContent'])->name('content.clone')->where('type', '[a-z][a-z0-9_-]*');

        // Autosave (shared by pages, posts, and custom content types)
        Route::post('/content/{id}/autosave',   [ContentController::class, 'autosave'])->name('content.autosave');
        Route::post('/content/{id}/autosave/discard', [ContentController::class, 'discardAutosave'])->name('content.autosave.discard');

        // Block operations (shared by pages and posts)
        Route::get('/content/{id}/blocks/new/{type}', [BlockController::class, 'editNew'])->name('blocks.new');
        Route::post('/content/{id}/blocks',           [BlockController::class, 'store'])->name('blocks.store');
        Route::get('/content/{id}/blocks/{blockId}',  [BlockController::class, 'edit'])->name('blocks.edit');
        Route::put('/content/{id}/blocks/{blockId}',  [BlockController::class, 'update'])->name('blocks.update');
        Route::delete('/content/{id}/blocks/{blockId}', [BlockController::class, 'destroy'])->name('blocks.destroy');
        Route::post('/content/{id}/blocks/reorder',   [BlockController::class, 'reorder'])->name('blocks.reorder');
        Route::post('/content/{id}/blocks/{blockId}/toggle', [BlockController::class, 'toggle'])->name('blocks.toggle');

        // Field Groups (Custom Fields library)
        Route::get('/field-groups',                [FieldGroupController::class, 'index'])->middleware('contensio.permission:fields.manage')->name('field-groups.index');
        Route::get('/field-groups/create',         [FieldGroupController::class, 'create'])->middleware('contensio.permission:fields.manage')->name('field-groups.create');
        Route::post('/field-groups',               [FieldGroupController::class, 'store'])->middleware('contensio.permission:fields.manage')->name('field-groups.store');
        Route::get('/field-groups/{id}/edit',      [FieldGroupController::class, 'edit'])->middleware('contensio.permission:fields.manage')->name('field-groups.edit');
        Route::put('/field-groups/{id}',           [FieldGroupController::class, 'update'])->middleware('contensio.permission:fields.manage')->name('field-groups.update');
        Route::delete('/field-groups/{id}',        [FieldGroupController::class, 'destroy'])->middleware('contensio.permission:fields.manage')->name('field-groups.destroy');

        // Fields (nested under a group)
        Route::post('/field-groups/{group}/fields',          [FieldController::class, 'store'])->middleware('contensio.permission:fields.manage')->name('fields.store');
        Route::put('/fields/{id}',                            [FieldController::class, 'update'])->middleware('contensio.permission:fields.manage')->name('fields.update');
        Route::delete('/fields/{id}',                         [FieldController::class, 'destroy'])->middleware('contensio.permission:fields.manage')->name('fields.destroy');
        Route::post('/field-groups/{group}/fields/reorder',  [FieldController::class, 'reorder'])->middleware('contensio.permission:fields.manage')->name('fields.reorder');

        // Content Types
        Route::get('/content-types',               [ContentTypeController::class, 'index'])->name('content-types.index');
        Route::get('/content-types/create',        [ContentTypeController::class, 'create'])->name('content-types.create');
        Route::post('/content-types',              [ContentTypeController::class, 'store'])->name('content-types.store');
        Route::get('/content-types/{id}/edit',     [ContentTypeController::class, 'edit'])->name('content-types.edit');
        Route::put('/content-types/{id}',          [ContentTypeController::class, 'update'])->name('content-types.update');
        Route::delete('/content-types/{id}',       [ContentTypeController::class, 'destroy'])->name('content-types.destroy');

        // Taxonomies (nested under a content type)
        Route::get('/content-types/{typeId}/taxonomies/create',      [TaxonomyController::class, 'create'])->name('taxonomies.create');
        Route::post('/content-types/{typeId}/taxonomies',             [TaxonomyController::class, 'store'])->name('taxonomies.store');
        Route::get('/content-types/{typeId}/taxonomies/{id}/edit',   [TaxonomyController::class, 'edit'])->name('taxonomies.edit');
        Route::put('/content-types/{typeId}/taxonomies/{id}',        [TaxonomyController::class, 'update'])->name('taxonomies.update');
        Route::delete('/content-types/{typeId}/taxonomies/{id}',     [TaxonomyController::class, 'destroy'])->name('taxonomies.destroy');

        // Terms (within a taxonomy)
        Route::get('/taxonomies/{taxonomyId}/terms',            [TermController::class, 'index'])->name('terms.index');
        Route::get('/taxonomies/{taxonomyId}/terms/create',     [TermController::class, 'create'])->name('terms.create');
        Route::post('/taxonomies/{taxonomyId}/terms',           [TermController::class, 'store'])->name('terms.store');
        Route::get('/taxonomies/{taxonomyId}/terms/{id}/edit',  [TermController::class, 'edit'])->name('terms.edit');
        Route::put('/taxonomies/{taxonomyId}/terms/{id}',       [TermController::class, 'update'])->name('terms.update');
        Route::delete('/taxonomies/{taxonomyId}/terms/{id}',    [TermController::class, 'destroy'])->name('terms.destroy');

        // Media
        Route::get('/media',                  [MediaController::class, 'index'])->name('media.index');
        Route::post('/media/upload',          [MediaController::class, 'upload'])->name('media.upload');
        Route::post('/media/bulk-destroy',    [MediaController::class, 'bulkDestroy'])->name('media.bulk-destroy');
        Route::delete('/media/{id}',          [MediaController::class, 'destroy'])->name('media.destroy');
        // JSON endpoints for the Media Library picker modal
        Route::get('/media/pick',         [MediaController::class, 'pick'])->name('media.pick');
        Route::post('/media/pick/upload', [MediaController::class, 'pickUpload'])->name('media.pick.upload');

        // Appearance — Themes
        Route::get('/themes',                    [ThemeController::class, 'index'])->name('themes.index');
        Route::get('/themes/screenshot',         [ThemeController::class, 'screenshot'])->name('themes.screenshot');
        Route::post('/themes/activate',          [ThemeController::class, 'activate'])->name('themes.activate');
        Route::post('/themes/install',           [ThemeController::class, 'install'])->name('themes.install');
        Route::post('/themes/uninstall',         [ThemeController::class, 'uninstall'])->name('themes.uninstall');
        Route::get('/themes/customize',          [ThemeController::class, 'customize'])->name('themes.customize');
        Route::post('/themes/customize',         [ThemeController::class, 'saveCustomize'])->name('themes.customize.save');
        Route::post('/themes/customize/reset',   [ThemeController::class, 'resetCustomize'])->name('themes.customize.reset');

        // Appearance — Menus
        Route::get('/menus',                        [MenuController::class, 'index'])->name('menus.index');
        Route::post('/menus',                       [MenuController::class, 'store'])->name('menus.store');
        Route::get('/menus/{id}/edit',              [MenuController::class, 'edit'])->name('menus.edit');
        Route::put('/menus/{id}',                   [MenuController::class, 'update'])->name('menus.update');
        Route::delete('/menus/{id}',                [MenuController::class, 'destroy'])->name('menus.destroy');
        Route::post('/menus/{id}/items',            [MenuController::class, 'addItem'])->name('menus.items.add');
        Route::post('/menus/{id}/items/reorder',    [MenuController::class, 'reorderItems'])->name('menus.items.reorder');

        // Users
        Route::get('/users',                [UserController::class, 'index'])->middleware('contensio.permission:users.view')->name('users.index');
        Route::get('/users/create',         [UserController::class, 'create'])->middleware('contensio.permission:users.create')->name('users.create');
        Route::post('/users',               [UserController::class, 'store'])->middleware('contensio.permission:users.create')->name('users.store');
        Route::get('/users/{id}/edit',      [UserController::class, 'edit'])->middleware('contensio.permission:users.update')->name('users.edit');
        Route::put('/users/{id}',           [UserController::class, 'update'])->middleware('contensio.permission:users.update')->name('users.update');
        Route::delete('/users/{id}',        [UserController::class, 'destroy'])->middleware('contensio.permission:users.delete')->name('users.destroy');

        // Roles
        Route::get('/roles',                [RoleController::class, 'index'])->middleware('contensio.permission:roles.manage')->name('roles.index');
        Route::get('/roles/create',         [RoleController::class, 'create'])->middleware('contensio.permission:roles.manage')->name('roles.create');
        Route::post('/roles',               [RoleController::class, 'store'])->middleware('contensio.permission:roles.manage')->name('roles.store');
        Route::get('/roles/{id}/edit',      [RoleController::class, 'edit'])->middleware('contensio.permission:roles.manage')->name('roles.edit');
        Route::put('/roles/{id}',           [RoleController::class, 'update'])->middleware('contensio.permission:roles.manage')->name('roles.update');
        Route::delete('/roles/{id}',        [RoleController::class, 'destroy'])->middleware('contensio.permission:roles.manage')->name('roles.destroy');

        // Settings / configuration hub
        Route::get('/settings',          [SettingController::class, 'index'])->name('settings.index');
        Route::get('/settings/general',  [SettingController::class, 'general'])->name('settings.general');
        Route::post('/settings/general', [SettingController::class, 'saveGeneral'])->name('settings.general.save');
        Route::get('/settings/seo',      [SettingController::class, 'seo'])->name('settings.seo');
        Route::post('/settings/seo',     [SettingController::class, 'saveSeo'])->name('settings.seo.save');
        Route::get('/settings/email',        [SettingController::class, 'email'])->name('settings.email');
        Route::post('/settings/email',       [SettingController::class, 'saveEmail'])->name('settings.email.save');
        Route::post('/settings/email/test',  [SettingController::class, 'sendTestEmail'])->name('settings.email.test');

        // White-label
        Route::get('/settings/whitelabel',                  [WhitelabelController::class, 'index'])->name('settings.whitelabel');
        Route::post('/settings/whitelabel/license',         [WhitelabelController::class, 'saveLicense'])->name('settings.whitelabel.license');
        Route::post('/settings/whitelabel/license/remove',  [WhitelabelController::class, 'removeLicense'])->name('settings.whitelabel.license.remove');
        Route::post('/settings/whitelabel/branding',        [WhitelabelController::class, 'saveBranding'])->name('settings.whitelabel.branding');
        Route::post('/settings/whitelabel/branding/reset',  [WhitelabelController::class, 'resetBranding'])->name('settings.whitelabel.branding.reset');

        // Languages
        Route::get('/languages',               [LanguageController::class, 'index'])->name('languages.index');
        Route::get('/languages/create',        [LanguageController::class, 'create'])->name('languages.create');
        Route::post('/languages',              [LanguageController::class, 'store'])->name('languages.store');
        Route::get('/languages/{id}/edit',     [LanguageController::class, 'edit'])->name('languages.edit');
        Route::put('/languages/{id}',          [LanguageController::class, 'update'])->name('languages.update');
        Route::delete('/languages/{id}',       [LanguageController::class, 'destroy'])->name('languages.destroy');
        Route::post('/languages/{id}/default', [LanguageController::class, 'setDefault'])->name('languages.default');

        // Contact page — settings + field builder
        Route::get('/contact',                    [ContactController::class, 'index'])->name('contact.index');
        Route::post('/contact/builder',           [ContactController::class, 'saveBuilder'])->name('contact.builder');
        Route::post('/contact/appearance',        [ContactController::class, 'saveAppearance'])->name('contact.appearance');
        Route::post('/contact/settings',          [ContactController::class, 'saveSettings'])->name('contact.settings');
        Route::post('/contact/fields',            [ContactController::class, 'storeField'])->name('contact.fields.store');
        Route::put('/contact/fields/{id}',        [ContactController::class, 'updateField'])->name('contact.fields.update');
        Route::delete('/contact/fields/{id}',     [ContactController::class, 'destroyField'])->name('contact.fields.destroy');
        Route::post('/contact/fields/reorder',    [ContactController::class, 'reorderFields'])->name('contact.fields.reorder');

        // Contact messages inbox
        Route::get('/contact/messages',                [ContactMessageController::class, 'index'])->name('contact.messages.index');
        Route::get('/contact/messages/export',         [ContactMessageController::class, 'export'])->name('contact.messages.export');
        Route::get('/contact/messages/{id}',           [ContactMessageController::class, 'show'])->name('contact.messages.show');
        Route::delete('/contact/messages/{id}',        [ContactMessageController::class, 'destroy'])->name('contact.messages.destroy');
        Route::post('/contact/messages/bulk',          [ContactMessageController::class, 'bulk'])->name('contact.messages.bulk');

        // Contact labels management
        Route::get('/contact/labels',                  [ContactLabelController::class, 'index'])->name('contact.labels.index');
        Route::post('/contact/labels',                 [ContactLabelController::class, 'store'])->name('contact.labels.store');
        Route::put('/contact/labels/{id}',             [ContactLabelController::class, 'update'])->name('contact.labels.update');
        Route::delete('/contact/labels/{id}',          [ContactLabelController::class, 'destroy'])->name('contact.labels.destroy');
        Route::post('/contact/labels/bulk-assign',     [ContactLabelController::class, 'bulkAssign'])->name('contact.labels.bulk-assign');

        // Assign / remove labels on a single message (AJAX)
        Route::post('/contact/messages/{id}/labels',              [ContactLabelController::class, 'attachToMessage'])->name('contact.messages.labels.attach');
        Route::delete('/contact/messages/{id}/labels/{labelId}',  [ContactLabelController::class, 'detachFromMessage'])->name('contact.messages.labels.detach');

        // Content review queue (approval workflow)
        Route::get('/reviews',              [ReviewController::class, 'index'])->name('reviews.index');
        Route::post('/reviews/{id}/approve',[ReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('/reviews/{id}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');

        // Comments moderation
        Route::get('/comments',                  [CommentController::class, 'index'])->middleware('contensio.permission:comments.manage')->name('comments.index');
        Route::post('/comments/bulk',            [CommentController::class, 'bulk'])->middleware('contensio.permission:comments.manage')->name('comments.bulk');
        Route::post('/comments/{id}/approve',    [CommentController::class, 'approve'])->middleware('contensio.permission:comments.manage')->name('comments.approve');
        Route::post('/comments/{id}/spam',       [CommentController::class, 'spam'])->middleware('contensio.permission:comments.manage')->name('comments.spam');
        Route::post('/comments/{id}/trash',      [CommentController::class, 'trash'])->middleware('contensio.permission:comments.manage')->name('comments.trash');
        Route::post('/comments/{id}/restore',    [CommentController::class, 'restore'])->middleware('contensio.permission:comments.manage')->name('comments.restore');
        Route::delete('/comments/{id}',          [CommentController::class, 'destroy'])->middleware('contensio.permission:comments.manage')->name('comments.destroy');

        // Comments settings
        Route::get('/settings/comments',         [SettingController::class, 'comments'])->name('settings.comments');
        Route::post('/settings/comments',        [SettingController::class, 'saveComments'])->name('settings.comments.save');

        // Users & registration settings
        Route::get('/settings/users',            [SettingController::class, 'users'])->name('settings.users');
        Route::post('/settings/users',           [SettingController::class, 'saveUsers'])->name('settings.users.save');
        Route::get('/settings/reading',          [SettingController::class, 'reading'])->name('settings.reading');
        Route::post('/settings/reading',         [SettingController::class, 'saveReading'])->name('settings.reading.save');

        // Activity log (read-only audit trail)
        Route::get('/activity-log', [ActivityLogController::class, 'index'])
            ->middleware('contensio.permission:activity_log.view')
            ->name('activity-log.index');

        // Redirects (SEO)
        Route::get('/redirects',                [RedirectController::class, 'index'])->middleware('contensio.permission:seo.manage_redirects')->name('redirects.index');
        Route::get('/redirects/create',         [RedirectController::class, 'create'])->middleware('contensio.permission:seo.manage_redirects')->name('redirects.create');
        Route::post('/redirects',               [RedirectController::class, 'store'])->middleware('contensio.permission:seo.manage_redirects')->name('redirects.store');
        Route::get('/redirects/{id}/edit',      [RedirectController::class, 'edit'])->middleware('contensio.permission:seo.manage_redirects')->name('redirects.edit');
        Route::put('/redirects/{id}',           [RedirectController::class, 'update'])->middleware('contensio.permission:seo.manage_redirects')->name('redirects.update');
        Route::delete('/redirects/{id}',        [RedirectController::class, 'destroy'])->middleware('contensio.permission:seo.manage_redirects')->name('redirects.destroy');

        // Tools — Import / Export
        Route::get('/tools/import-export',  [ImportExportController::class, 'index'])->name('tools.import-export');
        Route::post('/tools/export',        [ImportExportController::class, 'export'])->name('tools.export');
        Route::post('/tools/import',        [ImportExportController::class, 'import'])->name('tools.import');

        // Widgets
        Route::get('/widgets',                          [WidgetController::class, 'index'])->name('contensio.widgets');
        Route::post('/widgets',                         [WidgetController::class, 'store'])->name('contensio.widgets.store');
        Route::patch('/widgets/{widget}',               [WidgetController::class, 'update'])->name('contensio.widgets.update');
        Route::patch('/widgets/{widget}/toggle',        [WidgetController::class, 'toggle'])->name('contensio.widgets.toggle');
        Route::patch('/widgets/{widget}/move-up',       [WidgetController::class, 'moveUp'])->name('contensio.widgets.moveUp');
        Route::patch('/widgets/{widget}/move-down',     [WidgetController::class, 'moveDown'])->name('contensio.widgets.moveDown');
        Route::delete('/widgets/{widget}',              [WidgetController::class, 'destroy'])->name('contensio.widgets.destroy');

        // Tools — Backups
        Route::get('/tools/backups',                   [BackupController::class, 'index'])->name('tools.backups');
        Route::post('/tools/backups',                  [BackupController::class, 'store'])->name('tools.backups.store');
        Route::get('/tools/backups/{filename}/download',[BackupController::class, 'download'])->name('tools.backups.download');
        Route::delete('/tools/backups/{filename}',     [BackupController::class, 'destroy'])->name('tools.backups.destroy');
        Route::post('/tools/backups/restore/upload',   [BackupController::class, 'restoreUpload'])->name('tools.backups.restore-upload');
        Route::get('/tools/backups/restore/confirm',   [BackupController::class, 'restoreConfirm'])->name('tools.backups.restore-confirm');
        Route::post('/tools/backups/restore/execute',  [BackupController::class, 'restoreExecute'])->name('tools.backups.restore-execute');

        // Plugins
        Route::get('/plugins',                  [PluginController::class, 'index'])->name('plugins.index');
        Route::post('/plugins/enable',          [PluginController::class, 'enable'])->name('plugins.enable');
        Route::post('/plugins/disable',         [PluginController::class, 'disable'])->name('plugins.disable');
        Route::post('/plugins/install',         [PluginController::class, 'install'])->name('plugins.install');
        Route::post('/plugins/uninstall',       [PluginController::class, 'uninstall'])->name('plugins.uninstall');
        Route::get('/plugins/settings',         [PluginController::class, 'settings'])->name('plugins.settings');
        Route::post('/plugins/settings',        [PluginController::class, 'saveSettings'])->name('plugins.settings.save');
        Route::post('/plugins/settings/reset',  [PluginController::class, 'resetSettings'])->name('plugins.settings.reset');
        Route::post('/plugins/update',          [PluginController::class, 'update'])->name('plugins.update');
        Route::get('/plugins/browse',           [PluginController::class, 'browse'])->name('plugins.browse');
        Route::post('/plugins/install-from-directory', [PluginController::class, 'installFromDirectory'])->name('plugins.installFromDirectory');

    });
