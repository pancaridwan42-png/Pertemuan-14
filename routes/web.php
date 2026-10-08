<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\HomeController;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    // Profile dapat diakses semua user yang sudah login
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | ADMIN AREA
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->group(function () {

        // Admin dan Editor
        Route::middleware('role:admin,editor')->group(function () {
            Route::get('/news', [NewsController::class, 'index'])
                ->name('admin.news.index');

            Route::resource('categories', CategoryController::class);

            Route::resource('articles', ArticleController::class);
        });

        // Hanya Admin
        Route::middleware('role:admin')->group(function () {

            Route::get('/products', [ProductController::class, 'index'])
                ->name('admin.products.index');

            Route::resource('users', UserController::class);

            Route::get('/dashboard', function () {
                $total_berita = Article::count();
                $total_kategori = Category::count();
                $total_user = User::count();

                return view('admin.dashboard', compact(
                    'total_berita',
                    'total_kategori',
                    'total_user'
                ));
            })->name('admin.dashboard');
        });
    });
});

require __DIR__.'/auth.php';

Route::get('/home', [HomeController::class, 'index'])->name('home');