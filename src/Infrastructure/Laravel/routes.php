<?php

$backoffice_folder = getenv('BACKOFFICE_FOLDER');

if (is_null($backoffice_folder)) {
    $backoffice_folder = '/';
}

define('BACKOFFICE_FOLDER', $backoffice_folder);

Route::get($backoffice_folder, [
    'uses' => '\Osds\Backoffice\Application\Controllers\BackofficeController@index',
]);

Route::group(
    ['prefix' => $backoffice_folder . '/session'],
    function () {
        Route::get('/login', [
            'uses' => '\Osds\Backoffice\Application\Controllers\LoginController@login',
        ]);
        Route::post('/login', [
            'uses' => '\Osds\Backoffice\Application\Controllers\LoginController@postLogin',
        ]);
        Route::get('/logout', [
            'uses' => '\Osds\Backoffice\Application\Controllers\LoginController@logout',
        ]);
    }
);

Route::group(
    ['prefix' => $backoffice_folder.'/{model}'],
    function () {

        Route::get('/', [
            'uses' => '\Osds\Backoffice\Application\Controllers\BackofficeController@list',
        ]);

        Route::get('/create', [
            'uses' => '\Osds\Backoffice\Application\Controllers\BackofficeController@loadEmptyForm',
        ]);

        Route::post('/create', [
            'uses' => '\Osds\Backoffice\Application\Controllers\BackofficeController@create',
        ])->where('id', '[0-9]+');

        Route::get('/edit/{id}', [
            'uses' => '\Osds\Backoffice\Application\Controllers\BackofficeController@detail',
        ])->where('id', '[0-9]+');

        Route::post('/edit/{id}', [
            'uses' => '\Osds\Backoffice\Application\Controllers\BackofficeController@update',
        ])->where('id', '[0-9]+');

        Route::get('/delete/{id}', [
            'uses' => '\Osds\Backoffice\Application\Controllers\BackofficeController@delete',
        ])->where('id', '[0-9]+');

    }


);