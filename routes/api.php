<?php

use App\Http\Controllers\ContainerController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoicePrintController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\NameChangeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderPrintController;
use App\Http\Controllers\OutboundController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    // 文件下载（统一下载链接生成）
    Route::get('downloads/{type}/{id}', [DocumentDownloadController::class, 'show']);

    // 仪表盘
    Route::get('dashboard/stats', [DashboardController::class, 'getStats']);

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
    Route::get('inventory/inbounds', [InboundController::class, 'getInbounds']);
    Route::get('inventory/inbound/{id}', [InboundController::class, 'getInbound']);
    Route::post('inventory/inbound', [InboundController::class, 'createInbound']);
    Route::put('inventory/inbound/{id}', [InboundController::class, 'updateInbound']);
    Route::delete('inventory/inbound/{id}', [InboundController::class, 'deleteInbound']);
    Route::post('inventory/inbound/{id}/approve', [InboundController::class, 'approveInbound']);
    Route::post('inventory/inbound/{id}/reject', [InboundController::class, 'rejectInbound']);
    Route::get('inventory/inboundItems', [InboundController::class, 'getInboundItems']);

    // 入库报告
    Route::get('inventory/inboundReports', [InboundController::class, 'getInboundReports']);
    Route::post('inventory/inboundReport/generate', [InboundController::class, 'generateInboundReport']);
    Route::get('inventory/inboundReports/{id}/status', [InboundController::class, 'getInboundReportStatus']);
    Route::get('inventory/inboundReports/{id}/download', [InboundController::class, 'downloadInboundReport']);

    // 出库
    Route::get('inventory/outbounds', [OutboundController::class, 'getOutbounds']);
    Route::get('inventory/outbound/{id}', [OutboundController::class, 'getOutbound']);
    Route::post('inventory/outbound', [OutboundController::class, 'createOutbound']);
    Route::put('inventory/outbound/{id}', [OutboundController::class, 'updateOutbound']);
    Route::delete('inventory/outbound/{id}', [OutboundController::class, 'deleteOutbound']);
    Route::post('inventory/outbound/{id}/approve', [OutboundController::class, 'approveOutbound']);
    Route::post('inventory/outbound/{id}/reject', [OutboundController::class, 'rejectOutbound']);
    Route::get('inventory/outboundItems', [OutboundController::class, 'getOutboundItems']);

    // 出库报告
    Route::get('inventory/outboundReports', [OutboundController::class, 'getOutboundReports']);
    Route::post('inventory/outboundReport/generate', [OutboundController::class, 'generateOutboundReport']);
    Route::get('inventory/outboundReports/{id}/status', [OutboundController::class, 'getOutboundReportStatus']);
    Route::get('inventory/outboundReports/{id}/download', [OutboundController::class, 'downloadOutboundReport']);

    // 采购 - 注文书
    Route::get('procurement/orders', [OrderController::class, 'getOrders']);
    Route::post('procurement/order', [OrderController::class, 'createOrder']);
    Route::get('procurement/order/{id}', [OrderController::class, 'getOrder']);
    Route::put('procurement/order/{id}', [OrderController::class, 'updateOrder']);
    Route::delete('procurement/order/{id}', [OrderController::class, 'cancelOrder']);
    Route::post('procurement/order/{id}/submit', [OrderController::class, 'submitOrder']);
    Route::post('procurement/order/{id}/send', [OrderController::class, 'sendOrder']);
    Route::post('procurement/order/{id}/complete', [OrderController::class, 'completeOrder']);
    Route::post('procurement/order/{id}/cancel', [OrderController::class, 'cancelOrderAction']);

    // 采购 - 注文打印版
    Route::get('procurement/orderPrints', [OrderPrintController::class, 'getOrderPrints']);
    Route::post('procurement/orderPrint/generate', [OrderPrintController::class, 'generateOrderPrint']);
    Route::get('procurement/orderPrints/{printId}/status', [OrderPrintController::class, 'getOrderPrintStatus']);
    Route::get('procurement/orderPrints/{printId}/download', [OrderPrintController::class, 'downloadOrderPrint']);

    // 结算 - 发票
    Route::get('billing/invoices', [InvoiceController::class, 'getInvoices']);
    Route::post('billing/invoice', [InvoiceController::class, 'createInvoice']);
    Route::get('billing/invoice/{id}', [InvoiceController::class, 'getInvoice']);
    Route::post('billing/invoice/{id}/issue', [InvoiceController::class, 'issueInvoice']);
    Route::post('billing/invoice/{id}/cancel', [InvoiceController::class, 'cancelInvoice']);

    // 结算 - 发票打印版
    Route::get('billing/invoicePrints', [InvoicePrintController::class, 'getInvoicePrints']);
    Route::post('billing/invoicePrint/generate', [InvoicePrintController::class, 'generateInvoicePrint']);
    Route::get('billing/invoicePrints/{printId}/status', [InvoicePrintController::class, 'getInvoicePrintStatus']);
    Route::get('billing/invoicePrints/{printId}/download', [InvoicePrintController::class, 'downloadInvoicePrint']);

    // 名义变更
    Route::get('inventory/nameChanges', [NameChangeController::class, 'getNameChanges']);
    Route::get('inventory/nameChange/{id}', [NameChangeController::class, 'getNameChange']);
    Route::post('inventory/nameChange', [NameChangeController::class, 'createNameChange']);
    Route::put('inventory/nameChange/{id}', [NameChangeController::class, 'updateNameChange']);
    Route::delete('inventory/nameChange/{id}', [NameChangeController::class, 'deleteNameChange']);
    Route::post('inventory/nameChange/{id}/approve', [NameChangeController::class, 'approveNameChange']);
    Route::post('inventory/nameChange/{id}/reject', [NameChangeController::class, 'rejectNameChange']);
    Route::get('inventory/nameChangeItems', [NameChangeController::class, 'getNameChangeItems']);

    // 名义变更报告
    Route::get('inventory/nameChangeReports', [NameChangeController::class, 'getNameChangeReports']);
    Route::post('inventory/nameChangeReport/generate', [NameChangeController::class, 'generateNameChangeReport']);
    Route::get('inventory/nameChangeReports/{id}/status', [NameChangeController::class, 'getNameChangeReportStatus']);
    Route::get('inventory/nameChangeReports/{id}/download', [NameChangeController::class, 'downloadNameChangeReport']);

    // 库存
    Route::get('inventory/list', [InventoryController::class, 'getList']);
    Route::get('inventory/item/{id}', [InventoryController::class, 'getDetail']);
    Route::get('inventory/agedItems', [InventoryController::class, 'getAgedItems']);
    Route::post('inventory/agedItems/{id}/mute', [InventoryController::class, 'muteItem']);

    // 集装箱
    Route::get('containers', [ContainerController::class, 'getContainers']);
    Route::get('container/{id}', [ContainerController::class, 'getContainer']);
    Route::post('container', [ContainerController::class, 'createContainer']);
    Route::put('container/{id}', [ContainerController::class, 'updateContainer']);
    Route::delete('container/{id}', [ContainerController::class, 'deleteContainer']);

    // 在库报告书
    Route::get('inventory/reports', [InventoryController::class, 'getReports']);
    Route::post('inventory/report/generate', [InventoryController::class, 'generateReport']);
    Route::get('inventory/reports/{id}/status', [InventoryController::class, 'getReportStatus']);
    Route::get('inventory/reports/{id}/download', [InventoryController::class, 'downloadReport']);


});
