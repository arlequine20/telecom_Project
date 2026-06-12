<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\ReportController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('landing');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register.show');
    Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
    Route::get('/password/reset', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/password/email', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/dashboard', [AuthController::class, 'dashboardRedirect'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('dashboard')->with('success', 'Email verified successfully.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'A new verification link has been sent to your email address.');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::get('/send/{phone}', [UserController::class, 'redirectToSend'])->name('send.to');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/wallet/top-up', [AdminController::class, 'showWalletTopUpForm'])->name('admin.wallet.topup');
        Route::post('/wallet/top-up', [AdminController::class, 'walletTopUp'])->name('admin.wallet.topup.submit');
        Route::get('/customers', [AdminController::class, 'customers'])->name('admin.customers');
        Route::delete('/customers/{customer}', [AdminController::class, 'destroyCustomer'])->name('admin.customers.destroy');
        Route::get('/sim-cards', [AdminController::class, 'simCards'])->name('admin.sim-cards');
        Route::get('/sim-cards/create', [AdminController::class, 'showCreateForm'])->name('admin.sim-cards.create');
        Route::post('/sim-cards', [AdminController::class, 'storeSimCard'])->name('admin.sim-cards.store');
        Route::get('/sim-cards/{sim}/assign', [AdminController::class, 'showAssignForm'])->name('admin.sim-cards.assign');
        Route::post('/sim-cards/{sim}/assign', [AdminController::class, 'assignSimCard'])->name('admin.sim-cards.assign.submit');
        Route::put('/sim-cards/{sim}/assign', [AdminController::class, 'assignSimCard'])->name('admin.sim-cards.assign.update');
        Route::get('/transactions/pending', [AdminController::class, 'pendingTransactions'])->name('admin.pending');
        Route::post('/transactions/{transaction}/approve', [AdminController::class, 'approveTransaction'])->name('admin.transactions.approve');
        Route::post('/transactions/{transaction}/cancel', [AdminController::class, 'cancelTransaction'])->name('admin.transactions.cancel');
        Route::delete('/transactions/{transaction}', [AdminController::class, 'destroyTransaction'])->name('admin.transactions.destroy');
        Route::get('/transactions/history', [AdminController::class, 'transactionHistory'])->name('admin.history');
        Route::get('/api-checker', [AdminController::class, 'apiChecker'])->name('admin.api-checker');
        Route::get('/sim-cards/{sim}/buy-data', [AdminController::class, 'showBuyDataForm'])->name('admin.sim-cards.buy-data');
        Route::post('/sim-cards/{sim}/buy-data', [AdminController::class, 'purchaseData'])->name('admin.sim-cards.buy-data.submit');

        // Reports
        Route::resource('reports', ReportController::class)
            ->only(['index', 'create', 'store', 'show', 'destroy'])
            ->names('admin.reports');
        Route::get('/reports/{report}/export-pdf', [ReportController::class, 'exportPdf'])->name('admin.reports.export-pdf');
        Route::get('/reports/{report}/export-csv', [ReportController::class, 'exportCsv'])->name('admin.reports.export-csv');
        Route::get('/reports/{report}/export-word', [ReportController::class, 'exportWord'])->name('admin.reports.export-word');
    });

    Route::prefix('user')->group(function () {
        Route::post('/transactions/{transaction}/reverse', [UserController::class, 'requestReversal'])->name('user.transactions.requestReversal');
        Route::get('/wallet/top-up', [UserController::class, 'showWalletTopUpForm'])->name('user.wallet.topup');
        Route::post('/wallet/top-up', [UserController::class, 'walletTopUp'])->name('user.wallet.topup.submit');
        Route::get('/sim-cards/{sim}/recharge', [UserController::class, 'showRechargeForm'])->name('user.sim.recharge');
        Route::post('/sim-cards/{sim}/recharge', [UserController::class, 'rechargeSimCard'])->name('user.sim.recharge.submit');
        Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
        Route::get('/transfer', [UserController::class, 'transfer'])->name('user.transfer');
        Route::get('/send/{phone}', [UserController::class, 'sendTo'])->name('user.send.to');
        Route::post('/transfer/send', [UserController::class, 'sendTransfer'])->name('user.send');
        Route::get('/history', [UserController::class, 'history'])->name('user.history');
        Route::get('/sim-cards', [UserController::class, 'mySimCards'])->name('user.sims');
        Route::get('/profile', [UserController::class, 'profile'])->name('user.profile');
        Route::put('/profile', [UserController::class, 'updateProfile'])->name('user.profile.update');
    });
});

Route::get('/debug/sims', function () {
    $sims = \App\Models\SimCard::with(['customer', 'customer.user'])->limit(10)->get();
    return response()->json([
        'total_sims' => \App\Models\SimCard::count(),
        'sims' => $sims->map(function($sim) {
            return [
                'id' => $sim->id,
                'phone_number' => $sim->phone_number,
                'status' => $sim->status,
                'customer_id' => $sim->customer_id,
                'customer_name' => $sim->customer?->user?->name ?? 'No customer',
            ];
        })
    ]);
});
