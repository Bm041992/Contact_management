<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Contacts;
use App\Http\Controllers\Custom_fields;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [Contacts::class, 'index'])->name('contacts.index');
// Route::get('/contacts', [Contacts::class, 'index'])->name('contacts.index');
Route::post('/contacts', [Contacts::class, 'store'])->name('contacts.store');
Route::post('/contacts/edit', [Contacts::class, 'edit'])->name('contacts.edit');
Route::put('/contacts/{contact}', [Contacts::class, 'update'])->name('contacts.update');
Route::post('/contacts/delete', [Contacts::class, 'destroy'])->name('contacts.destroy');
Route::get('/contacts/list', [Contacts::class, 'getLists'])->name('contacts.getLists');
Route::get('/contacts/active', [Contacts::class, 'getActiveContacts'])->name('contacts.active');
Route::post('/contacts/merge_contacts', [Contacts::class, 'merge_contacts'])->name('contacts.merge_contacts');

Route::get('/contacts/custom-fields', [Contacts::class, 'customFields'])->name('contacts.customFields');
Route::get('custom-fields', [Custom_fields::class, 'index'])->name('custom-fields.index');

Route::post('custom-fields', [Custom_fields::class, 'store'])->name('custom-fields.store');
Route::post('custom-fields/edit', [Custom_fields::class, 'edit'])->name('custom-fields.edit');
Route::put('custom-fields/{id}', [Custom_fields::class, 'update'])->name('custom-fields.update');

Route::post('custom-fields/delete', [Custom_fields::class, 'destroy'])->name('custom-fields.destroy');