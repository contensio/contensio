<?php

use Contensio\Cms\Http\Controllers\Admin\BlockController;
use Contensio\Cms\Http\Controllers\Admin\ContentController;
use Contensio\Cms\Http\Controllers\Admin\ContentTypeController;
use Contensio\Cms\Http\Controllers\Admin\TaxonomyController;
use Contensio\Cms\Http\Controllers\Admin\TermController;
use Contensio\Cms\Http\Controllers\Admin\DashboardController;
use Contensio\Cms\Http\Controllers\Admin\LanguageController;
use Contensio\Cms\Http\Controllers\Admin\MediaController;
use Contensio\Cms\Http\Controllers\Admin\MenuController;
use Contensio\Cms\Http\Controllers\Admin\PluginController;
use Contensio\Cms\Http\Controllers\Admin\SettingController;
use Contensio\Cms\Http\Controllers\Admin\ThemeController;
use Contensio\Cms\Http\Controllers\Admin\UserController;
use Contensio\Cms\Http\Controllers\Auth\LoginController;
use Contensio\Cms\Http\Controllers\Frontend\FrontendController;
use Illuminate\Support\Facades\Route;

// ─── Frontend ──────────────────────────────────────────────────────────────
Route::middleware('web')->group(function () {
    Route::get('/',              [FrontendController::class, 'home'])->name('cms.home');
    Route::get('/blog',          [FrontendController::class, 'archive'])->name('cms.blog');
    Route::get('/blog/{slug}',   [FrontendController::class, 'post'])->name('cms.post');
    Route::get('/{slug}',        [FrontendController::class, 'page'])->name('cms.page');
});

// ─── Auth ──────────────────────────────────────────────────────────────────
Route::middleware('web')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLogin'])->name('cms.login');
    Route::post('/login', [LoginController::class, 'login'])->name('cms.login.store');
    Route::post('/logout', [LoginController::class, 'logout'])->name('cms.logout')->middleware('auth');
});

// ─── Admin panel ───────────────────────────────────────────────────────────
Route::prefix(config('cms.route_prefix'))
    ->name('cms.admin.')
    ->middleware(['web', 'cms.auth', 'cms.admin'])
    ->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Pages
        Route::get('/pages',             [ContentController::class, 'pages'])->name('pages.index');
        Route::get('/pages/create',      [ContentController::class, 'createPage'])->name('pages.create');
        Route::post('/pages',            [ContentController::class, 'storePage'])->name('pages.store');
        Route::get('/pages/{id}/edit',   [ContentController::class, 'editPage'])->name('pages.edit');
        Route::put('/pages/{id}',        [ContentController::class, 'updatePage'])->name('pages.update');
        Route::delete('/pages/{id}',     [ContentController::class, 'destroyPage'])->name('pages.destroy');

        // Posts
        Route::get('/posts',             [ContentController::class, 'posts'])->name('posts.index');
        Route::get('/posts/create',      [ContentController::class, 'createPost'])->name('posts.create');
        Route::post('/posts',            [ContentController::class, 'storePost'])->name('posts.store');
        Route::get('/posts/{id}/edit',   [ContentController::class, 'editPost'])->name('posts.edit');
        Route::put('/posts/{id}',        [ContentController::class, 'updatePost'])->name('posts.update');
        Route::delete('/posts/{id}',     [ContentController::class, 'destroyPost'])->name('posts.destroy');

        // Custom content types (non-system) — type must start with a letter
        Route::get('/content/{type}',            [ContentController::class, 'indexContent'])->name('content.index')->where('type', '[a-z][a-z0-9_-]*');
        Route::get('/content/{type}/create',     [ContentController::class, 'createContent'])->name('content.create')->where('type', '[a-z][a-z0-9_-]*');
        Route::post('/content/{type}',           [ContentController::class, 'storeContent'])->name('content.store')->where('type', '[a-z][a-z0-9_-]*');
        Route::get('/content/{type}/{id}/edit',  [ContentController::class, 'editContent'])->name('content.edit')->where('type', '[a-z][a-z0-9_-]*');
        Route::put('/content/{type}/{id}',       [ContentController::class, 'updateContent'])->name('content.update')->where('type', '[a-z][a-z0-9_-]*');
        Route::delete('/content/{type}/{id}',    [ContentController::class, 'destroyContent'])->name('content.destroy')->where('type', '[a-z][a-z0-9_-]*');

        // Block operations (shared by pages and posts)
        Route::get('/content/{id}/blocks/new/{type}', [BlockController::class, 'editNew'])->name('blocks.new');
        Route::post('/content/{id}/blocks',           [BlockController::class, 'store'])->name('blocks.store');
        Route::get('/content/{id}/blocks/{blockId}',  [BlockController::class, 'edit'])->name('blocks.edit');
        Route::put('/content/{id}/blocks/{blockId}',  [BlockController::class, 'update'])->name('blocks.update');
        Route::delete('/content/{id}/blocks/{blockId}', [BlockController::class, 'destroy'])->name('blocks.destroy');
        Route::post('/content/{id}/blocks/reorder',   [BlockController::class, 'reorder'])->name('blocks.reorder');
        Route::post('/content/{id}/blocks/{blockId}/toggle', [BlockController::class, 'toggle'])->name('blocks.toggle');

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
        Route::get('/media',          [MediaController::class, 'index'])->name('media.index');
        Route::post('/media/upload',  [MediaController::class, 'upload'])->name('media.upload');
        Route::delete('/media/{id}',  [MediaController::class, 'destroy'])->name('media.destroy');

        // Appearance — Themes
        Route::get('/themes',                    [ThemeController::class, 'index'])->name('themes.index');
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
        Route::get('/users', [UserController::class, 'index'])->name('users.index');

        // Settings / configuration hub
        Route::get('/settings',          [SettingController::class, 'index'])->name('settings.index');
        Route::get('/settings/general',  [SettingController::class, 'general'])->name('settings.general');
        Route::post('/settings/general', [SettingController::class, 'saveGeneral'])->name('settings.general.save');

        // Languages
        Route::get('/languages',               [LanguageController::class, 'index'])->name('languages.index');
        Route::get('/languages/create',        [LanguageController::class, 'create'])->name('languages.create');
        Route::post('/languages',              [LanguageController::class, 'store'])->name('languages.store');
        Route::get('/languages/{id}/edit',     [LanguageController::class, 'edit'])->name('languages.edit');
        Route::put('/languages/{id}',          [LanguageController::class, 'update'])->name('languages.update');
        Route::delete('/languages/{id}',       [LanguageController::class, 'destroy'])->name('languages.destroy');
        Route::post('/languages/{id}/default', [LanguageController::class, 'setDefault'])->name('languages.default');

        // Plugins
        Route::get('/plugins',           [PluginController::class, 'index'])->name('plugins.index');
        Route::post('/plugins/enable',   [PluginController::class, 'enable'])->name('plugins.enable');
        Route::post('/plugins/disable',  [PluginController::class, 'disable'])->name('plugins.disable');
        Route::post('/plugins/install',  [PluginController::class, 'install'])->name('plugins.install');
        Route::post('/plugins/uninstall',[PluginController::class, 'uninstall'])->name('plugins.uninstall');

    });
