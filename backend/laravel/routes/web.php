<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\Auth\Web3LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\Teams\TeamInvitationController;
use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::get('/', [ApiController::class, 'index'])->name('home');

Route::post('login/web3', Web3LoginController::class)->name('web3.login');

Route::get('/wallet-login', fn () => inertia('auth/WalletLogin'))->name('wallet.login')->middleware('guest');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('invitations/{invitation}/accept', [TeamInvitationController::class, 'accept'])->name('invitations.accept');
    Route::resource('links', LinkController::class)->names([
        'index' => 'links',
        'store' => 'links.store',
        'update' => 'links.update',
        'destroy' => 'links.destroy',
    ]);
    Route::resource('categories', CategoryController::class)->except(['show', 'create', 'edit']);
});

require __DIR__.'/settings.php';
