<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>入庫依頼書</title>
    <style>
        @page { 
            margin: 15mm 10mm 15mm 10mm; 
        }
        body {
            font-family: "Noto Sans JP", "Hiragino Kaku Gothic ProN", "Yu Gothic", "Meiryo", sans-serif;
            font-size: 9px;
            line-height: 1.2;
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 100%;
            max-width: 100%;
        }
        
        /* 标题样式 */
        .title {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0 20px 0;
            letter-spacing: 1px;
        }
        
        /* 文档编号 */
        .document-no {
            text-align: right;
            font-size: 9px;
            margin-bottom: 20px;
        }
        
        /* 主表格样式 */
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 6px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 8px;
            height: 20px;
        }
        
        .main-table th {
            background-color: #f8f8f8;
            font-weight: bold;
            font-size: 8px;
        }
        
        .text-left {
            text-align: left;
            padding-left: 5px;
        }
        
        .text-right {
            text-align: right;
            padding-right: 5px;
        }
        
        .text-center {
            text-align: center;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        
        .empty-row {
            height: 20px;
        }
        
        /* 回复传真提示 */
        .reply-fax {
            color: #000;
            padding: 8px 0;
            text-align: left;
            font-size: 9px;
            margin-top: 20px;
        }
        
        /* 列宽设置 */
        .col-product { width: 20%; }
        .col-spec { width: 15%; }
        .col-unit { width: 10%; }
        .col-quantity { width: 12%; }
        .col-weight { width: 12%; }
        .col-lot { width: 12%; }
        .col-expiry { width: 12%; }
        .col-remarks { width: 7%; }
    </style>
</head>
<body>
    <div class="container">
        <!-- 标题 -->
        <div class="title">入庫依頼書</div>
        
        <!-- 文档编号 -->
        <div class="document-no">NO : {{ $inbound->inbound_order_id ?? 'RKMJ' . date('Ymd') }}</div>
        
        <!-- 主表格 -->
        <table class="main-table">
            <thead>
                <tr>
                    <th colspan="8" style="text-align: left; padding-left: 10px;">
                        (株)ベニレイ・ロジスティクス 東扇島<br>
                        御中<br>
                        FAX 044-266-9300
                    </th>
                </tr>
                <tr>
                    <th colspan="4" style="text-align: left; padding-left: 10px;">
                        作成日: {{ $reportDate }}<br>
                        {{ $customer->name ?? '株式会社マルオカジャパン' }}<br>
                        {{ $customer->detail_address1 ?? '東京都大田区山王1-5-3-209' }} {{ $customer->detail_address2 ?? 'メゾンド山王' }}<br>
                        TEL: {{ $customer->tel ?? '03-6780-6575' }} FAX: {{ $customer->fax ?? '03-6780-7326' }}
                    </th>
                    <th colspan="4" style="text-align: left; padding-left: 10px;">
                        入庫日: {{ $inbound->inbound_date ? \Carbon\Carbon::parse($inbound->inbound_date)->format('Y年n月j日') : date('Y年n月j日') }}
                    </th>
                </tr>
                <tr>
                    <th class="col-product">商品名</th>
                    <th class="col-spec">規格</th>
                    <th class="col-unit">単量</th>
                    <th class="col-quantity">個数</th>
                    <th class="col-weight">重量</th>
                    <th class="col-lot">LOT. NO</th>
                    <th class="col-expiry">賞味期限</th>
                    <th class="col-remarks">備考</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                <tr>
                    <td class="text-left">{{ $row->product_name }}</td>
                    <td class="text-left">{{ $row->specifications }}</td>
                    <td class="text-center">{{ $row->unit_quantity > 0 ? number_format($row->unit_quantity, 2) : '' }}</td>
                    <td class="text-center">
                        @if($row->quantity > 0)
                            {{ number_format($row->quantity) }}
                            @if($row->sub_quantity > 0)
                                <br>{{ number_format($row->sub_quantity) }}
                            @endif
                        @endif
                    </td>
                    <td class="text-center">{{ $row->weight > 0 ? number_format($row->weight, 0) . 'kg' : '' }}</td>
                    <td class="text-center">{{ $row->lot_number ?: '' }}</td>
                    <td class="text-center">
                        @if($row->best_before_date)
                            {{ $row->best_before_date->format('Y/m/d') }}
                        @endif
                    </td>
                    <td class="text-center">{{ $row->notes ?: '' }}</td>
                </tr>
                @endforeach
                
                <!-- 添加空行以匹配图片布局 -->
                @for($i = count($rows); $i < 5; $i++)
                <tr class="empty-row">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endfor
                
                <!-- 合计行 -->
                <tr class="total-row">
                    <td class="text-center">合計</td>
                    <td></td>
                    <td></td>
                    <td class="text-center">
                        @if($totals['quantity'] > 0)
                            {{ number_format($totals['quantity']) }}
                        @endif
                    </td>
                    <td class="text-center">
                        @if($totals['weight'] > 0)
                            {{ number_format($totals['weight'], 0) }}kg
                        @endif
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                
                <!-- 入庫名義先信息 -->
                <tr>
                    <td colspan="4" style="text-align: left; padding-left: 10px; padding-top: 15px;">
                        <strong>入庫名義先</strong><br>
                        {{ $customer->name ?? '株式会社マルオカジャパン' }}<br>
                        住所 {{ $customer->detail_address1 ?? '東京都大田区山王1-5-3-209' }} {{ $customer->detail_address2 ?? 'メゾンド山王' }}<br>
                        担当 {{ $customer->contact?->name ?? '陸泉荃' }}<br>
                        電話 {{ $customer->tel ?? '03-6780-6575' }}<br>
                        FAX {{ $customer->fax ?? '03-6780-7326' }}
                    </td>
                    <td colspan="4" style="text-align: left; padding-left: 10px; padding-top: 15px;">
                        <strong>保管料</strong><br>
                        {{ $inbound->inbound_date ? \Carbon\Carbon::parse($inbound->inbound_date)->format('Y年n月j日') : date('Y年n月j日') }}より、<br>
                        {{ $customer->name ?? '(株) マルオカジャパン' }} 負担
                    </td>
                </tr>
            </tbody>
        </table>
        
        <!-- 回复传真提示 -->
        <div class="reply-fax">
            返信FAX({{ $customer->fax ?? '03-6780-7326' }}) お願いします。
        </div>
    </div>
</body>
</html>
