<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\OutboundController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    // 商品
    Route::get('products', [ProductController::class, 'getProducts']);
    Route::get('product/{id}', [ProductController::class, 'getProduct']);
    Route::post('product', [ProductController::class, 'createProduct']);
    Route::put('product/{id}', [ProductController::class, 'updateProduct']);
    Route::delete('product/{id}', [ProductController::class, 'deleteProduct']);

    // 仓库
    Route::get('warehouses', [WarehouseController::class, 'getWarehouses']);
    Route::get('warehouse/{id}', [WarehouseController::class, 'getWarehouse']);
    Route::post('warehouse', [WarehouseController::class, 'createWarehouse']);
    Route::put('warehouse/{id}', [WarehouseController::class, 'updateWarehouse']);
    Route::delete('warehouse/{id}', [WarehouseController::class, 'deleteWarehouse']);

    // 客户
    Route::get('customers', [CustomerController::class, 'getCustomers']);
    Route::get('customer/{id}', [CustomerController::class, 'getCustomer']);
    Route::post('customer', [CustomerController::class, 'createCustomer']);
    Route::put('customer/{id}', [CustomerController::class, 'updateCustomer']);
    Route::delete('customer/{id}', [CustomerController::class, 'deleteCustomer']);

    // 入库
    Route::get('/inventory/inbounds', [InboundController::class, 'getInbounds']);
    Route::get('/inventory/inbound/{id}', [InboundController::class, 'getInbound']);
    Route::post('/inventory/inbound', [InboundController::class, 'createInbound']);
    Route::put('/inventory/inbound/{id}', [InboundController::class, 'updateInbound']);
    Route::delete('/inventory/inbound/{id}', [InboundController::class, 'deleteInbound']);
    Route::post('inventory/inbound/{id}/approve', [InboundController::class, 'approveInbound']);
    Route::post('inventory/inbound/{id}/reject', [InboundController::class, 'rejectInbound']);

    // 出库
    Route::get('/inventory/outbounds', [OutboundController::class, 'getOutbounds']);
    Route::get('/inventory/outbound/{id}', [OutboundController::class, 'getOutbound']);
    Route::post('/inventory/outbound', [OutboundController::class, 'createOutbound']);
    Route::put('/inventory/outbound/{id}', [OutboundController::class, 'updateOutbound']);
    Route::delete('/inventory/outbound/{id}', [OutboundController::class, 'deleteOutbound']);
    Route::post('inventory/outbound/{id}/approve', [OutboundController::class, 'approveOutbound']);
    Route::post('inventory/outbound/{id}/reject', [OutboundController::class, 'rejectOutbound']);

    // 库存
    Route::get('/inventory/list', [InventoryController::class, 'getList'] );
});
