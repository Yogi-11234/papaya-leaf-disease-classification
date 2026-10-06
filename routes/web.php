<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClassificationController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\PohonController;

// Dashboard Analitik Model (Home)
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Upload & Inferensi
Route::get('/upload', [ClassificationController::class, 'index'])->name('klasifikasi.index');
Route::post('/klasifikasi', [ClassificationController::class, 'store'])->name('klasifikasi.store');
Route::get('/klasifikasi/{id}', [ClassificationController::class, 'show'])->name('klasifikasi.show');

// Riwayat & Manajemen Pohon
Route::get('/riwayat', [HistoryController::class, 'index'])->name('riwayat.index');
Route::delete('/riwayat/{id}', [HistoryController::class, 'destroy'])->name('riwayat.destroy');
Route::put('/pohon/{id}', [PohonController::class, 'update'])->name('pohon.update');
Route::patch('/klasifikasi/{id}/pohon', [HistoryController::class, 'updateTreeAssignment'])->name('klasifikasi.update_pohon');

// Chatbot AI Konsultasi
Route::get('/klasifikasi/{id}/chat', [ChatbotController::class, 'getChatHistory'])->name('chat.history');
Route::post('/klasifikasi/{id}/chat', [ChatbotController::class, 'sendMessage'])->name('chat.send');
Route::get('/pohon/{id}/chat', [ChatbotController::class, 'getTreeChatHistory'])->name('chat.tree_history');
Route::post('/pohon/{id}/chat', [ChatbotController::class, 'sendTreeMessage'])->name('chat.tree_send');
