<?php

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post("/user/login", "UserController@login");


Route::middleware('auth:api')->group(function() {

    // Объекты
    Route::get('building', 'BuildingController@all');
    Route::post('building', 'BuildingController@create');
    Route::get('building/analytics/{id?}', 'BuildingController@analytics');
    Route::get('building/gantt-chart/{id?}', 'BuildingController@ganttChart');
    Route::get('building/{id}', 'BuildingController@show');
    Route::put('building/{id}', 'BuildingController@update');
    Route::post('building/{id}/delete', 'BuildingController@delete');

    // Виды работ
    Route::get('work', 'WorkController@all');

    // Подрядчики
    Route::get('contractor', 'ContractorController@all');
    Route::post('contractor', 'ContractorController@create');
    Route::get('contractor/names', 'ContractorController@names');
    Route::get('contractor/{id}/building/{building_id}/analytics', 'ContractorController@analytic');
    Route::get('contractor/{id}/building/{building_id}/finance', 'ContractorController@finance');
    Route::get('contractor/{id}', 'ContractorController@show');
    Route::put('contractor/{id}', 'ContractorController@edit');
    Route::post('contractor/{id}/delete', 'ContractorController@delete');

    // Список проверок
    Route::get('check-list', 'CheckListController@all');
    Route::get('check-list/{id}/show', 'CheckListController@show');
    Route::post('check-list', 'CheckListController@create');
    Route::put('check-list/{id}', 'CheckListController@update');
    Route::post('check-list/{id}/marker', 'CheckListController@addItem');
    Route::post('check-list/{id}/email', 'CheckListController@prescriptionMail');
    Route::get('check-list/marker/{id}', 'CheckListController@showItem');
    Route::put('check-list/marker/{id}', 'CheckListController@updateItem');
    Route::post('check-list/marker/status', 'CheckListController@updateStatusItems');
    Route::get('check-list/marker/{id}/schema', 'CheckListController@schemaItem');
    Route::get('check-list/{id}/pdf', 'CheckListController@pdf');
    Route::get('check-list/{id}/schema', 'CheckListController@schema');
    Route::get('check-list/renouncement/{id}', 'CheckListController@showRenouncement');
    Route::get('check-list/renouncement/{id}/pdf', 'CheckListController@renouncementPdf');
    Route::post('check-list/renouncement/{id}', 'CheckListController@createRenouncement');
    Route::get('check-list/contractor_representative', 'CheckListController@contractorRepresentative');
    Route::get('check-list/demands', 'CheckListController@demands');
    Route::get('check-list/floors', 'CheckListController@floors');
    Route::get('check-list/contractors', 'CheckListController@contractors');
    Route::post('check-list/{id}/delete', 'CheckListController@delete');

    // Работа с файлами
    Route::post('file/upload', 'FileController@upload');
    Route::get('file/image/{filename}', 'FileController@image')
        ->middleware('cache.headers:private;max_age=432000')
        ->name('file.image.show');
    Route::get('file/thumbnail/{filename}', 'FileController@thumbnail')
        ->middleware('cache.headers:private;max_age=432000')
        ->name('file.image.thumbnail');
    Route::get('file/{folder}/{filename}', 'FileController@file')->name('file.show');

    // Справочники
    Route::get('handbook/building', 'BuildingHandbookController@building');
    Route::get('handbook/building/work/{id}', 'BuildingHandbookController@byWork');

    // Пользователи
    Route::get('/user', function(Request $request) {
        return $request->user();
    });
    Route::get('user/all', 'UserController@all');
    Route::get('user/roles', 'UserController@roles');
    Route::get('user/{id}', 'UserController@userInfo');
    Route::put('user/{id}', 'UserController@edit');
    Route::post('user/create', 'UserController@create');
    Route::post('user/{id}/password/reset', 'UserController@passwordReset');
    Route::post('user/{id}/remove', 'UserController@remove');

    // История изменений
    Route::get('history', 'HistoryController@all');
    Route::get('history/{id}', 'HistoryController@show');
    Route::get('history/user/{id}', 'HistoryController@byUser');

    // Push
    Route::get('push', 'PushController@all');
    Route::post('push/{id}/reading', 'PushController@reading');
    Route::post('push/readings', 'PushController@readings');
    Route::post('push/token', 'FirebaseController@saveToken');

});
