<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EbookController;
use App\Http\Controllers\EbookShareController;
use App\Http\Controllers\KarateAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\HomeController;

/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/

// Route::get('/', function () {
//     return redirect()->route('login');
// });
Route::get('/', [HomeController::class, 'userHome']);
Route::get('/home', [HomeController::class, 'userHome'])
    ->name('home');
    
/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

Route::get('/login', function () {
    if (session()->has('logged_in')) {
        return redirect('/home');
    }

    return view('auth.login');
})->name('login');

Route::post('/karate-login', [KarateAuthController::class, 'login'])
    ->name('karate.login');

Route::post('/logout', [KarateAuthController::class, 'logout'])
    ->name('logout');

Route::get('/ebook/{slug}', [EbookController::class, 'view'])
    ->name('ebook.view');

Route::get('/ebook/{slug}/download', [EbookController::class, 'download'])
    ->name('ebook.download');


/*
|--------------------------------------------------------------------------
| Protected (All Logged Users)
|--------------------------------------------------------------------------
*/

Route::middleware(['karate.auth'])->group(function () {

    // Route::get('/home', [HomeController::class, 'userHome']);
    Route::get('/websites', [HomeController::class, 'websites'])
        ->name('websites.index');
    Route::get('/ebooks/category/{category}', [HomeController::class, 'browseCategory'])
        ->name('ebooks.category');
    Route::get('/reported-issues', [HomeController::class, 'reportedIssues'])
        ->name('reported-issues.index');
Route::get('/karate-ebooks', function () {
    // Redirect to home with category=4 (Karate eBooks category ID)
    return redirect('/home?category=4#ebooksSection');
})->name('karate.ebooks');

    /* ---------------- Dashboard ---------------- */

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->middleware('admin')
        ->name('admin.dashboard');
        
    Route::get('/get-subcategories/{id}', function ($id) {
        return \App\Models\Category::where('parent_id', $id)->get();
    });

    Route::post('/ebook/{id}/report-issue', [EbookController::class, 'reportIssue'])
        ->name('ebook.report-issue');

    /* ---------------- Upload ---------------- */

    Route::post('/ebooks/upload',
        [EbookController::class, 'store']
    )->middleware('can.upload');


    /* ---------------- Share ---------------- */

    Route::post('/ebooks/share/{id}',
        [EbookShareController::class, 'generate']
    )->middleware('can.share');


    /* ---------------- Delete ---------------- */

    Route::delete('/ebook/delete/{id}',
        [EbookController::class, 'delete']
    );

});


/*
|--------------------------------------------------------------------------
| Admin Only
|--------------------------------------------------------------------------
*/

Route::middleware(['karate.auth','admin'])->group(function () {

    Route::get('/admin/ebooks', [AdminController::class, 'ebooks'])
        ->name('admin.ebooks');
    Route::get('/admin/ebooks/{id}/edit', [AdminController::class, 'editEbook'])
        ->name('admin.ebooks.edit');
    Route::put('/admin/ebooks/{id}', [AdminController::class, 'updateEbook'])
        ->name('admin.ebooks.update');

    Route::get('/admin/today-uploads', [AdminController::class, 'todayUploads'])
        ->name('admin.todayUploads');

    Route::get('/admin/expired-shares', [AdminController::class, 'expiredShares'])
        ->name('admin.expiredShares');

    Route::get('/admin/categories', [AdminController::class, 'categories'])
        ->name('admin.categories');

    Route::get('/admin/menus', [AdminController::class, 'menus'])
        ->name('admin.menus');

    Route::post('/admin/menus', [AdminController::class, 'storeMenu'])
        ->name('admin.menus.store');

    Route::put('/admin/menus/{id}', [AdminController::class, 'updateMenu'])
        ->name('admin.menus.update');

    Route::post('/admin/menus/{id}/toggle', [AdminController::class, 'toggleMenu'])
        ->name('admin.menus.toggle');

    Route::delete('/admin/menus/{id}', [AdminController::class, 'deleteMenu'])
        ->name('admin.menus.delete');

    Route::post('/admin/categories/store-tree', [AdminController::class, 'storeCategoryTree'])
        ->name('admin.categories.storeTree');

    Route::put('/admin/categories/{id}', [AdminController::class, 'updateCategory'])
        ->name('admin.categories.update');

    Route::delete('/admin/categories/{id}', [AdminController::class, 'deleteCategory'])
        ->name('admin.categories.delete');
      

    Route::post('/admin/users/{id}/update',
        [UserController::class, 'update']
    )->name('admin.users.update');

    Route::delete('/admin/users/{id}',
        [UserController::class, 'destroy']
    )->name('admin.users.destroy');

    Route::post('/admin/users/{id}/reset-uploads',
        [AdminController::class, 'resetUserUploads']
    )->name('admin.users.resetUploads');

    Route::post('/admin/users/{id}/reset-shares',
        [AdminController::class, 'resetUserShares']
    )->name('admin.users.resetShares');
      /* Routes for User Mange*/
    Route::get('/admin/users', [UserController::class, 'index'])
        ->name('admin.users');
    Route::post('/admin/users/{id}/edit-details', [UserController::class, 'updateUserDetails'])
    ->name('admin.users.editDetails');

});


/*
|--------------------------------------------------------------------------
| Public Share View (No Login)
|--------------------------------------------------------------------------
*/

Route::get('/flip-book/{slug}',
    [EbookShareController::class, 'view']
)->name('ebook.share');

Route::get('/flip-book/{slug}/download',
    [EbookShareController::class, 'download']
)->name('ebook.share.download');

// Route::get('/ebook/download/{id}', [EbookController::class, 'download'])
//     ->name('ebook.download.legacy');
