<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>出庫依頼書</title>
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
        .col-inbound-lot { width: 15%; }
        .col-product { width: 20%; }
        .col-spec { width: 25%; }
        .col-original { width: 12%; }
        .col-quantity { width: 12%; }
        .col-remarks { width: 16%; }
    </style>
</head>
<body>
    <div class="container">
        <!-- 主表格 -->
        <table class="main-table">
            <thead>
                <tr>
                    <th colspan="6" style="text-align: left; padding-left: 10px;">
                        作成日: {{ $reportDate }}<br>
                        訂正日: /<br>
                        No.: {{ $outbound->outbound_order_id ?? 'MJ' . date('Ymd') }}
                    </th>
                </tr>
                <tr>
                    <th colspan="6" style="text-align: center; font-size: 16px; font-weight: bold; padding: 15px;">
                        出庫依頼書<br>
                        {{ $outbound->outbound_date ? \Carbon\Carbon::parse($outbound->outbound_date)->format('Y/m/d H時半') : date('Y/m/d H時半') }}
                    </th>
                </tr>
                <tr>
                    <th colspan="3" style="text-align: left; padding-left: 10px;">
                        (株)ベニレイ・ロジスティクス<br>
                        東扇島事務所<br>
                        FAX 044-266-9300<br>
                        出庫先: {{ $customer->name ?? '株式会社マルオカジャパン' }}<br>
                        御中
                    </th>
                    <th colspan="3" style="text-align: left; padding-left: 10px;">
                        (株){{ $customer->name ?? 'マルオカジャパン' }}<br>
                        TEL: {{ $customer->tel ?? '03-6780-6575' }}<br>
                        陸軍
                    </th>
                </tr>
                <tr>
                    <th colspan="3" style="text-align: left; padding-left: 10px;">
                        扱便: 自社便<br>
                        保管料: /
                    </th>
                    <th colspan="3" style="text-align: left; padding-left: 10px;">
                        No. {{ $outbound->outbound_order_id ?? 'MJ' . date('Ymd') }}
                    </th>
                </tr>
                <tr>
                    <th class="col-inbound-lot">入庫/LOT番号</th>
                    <th class="col-product">品名</th>
                    <th class="col-spec">規格、荷姿</th>
                    <th class="col-original">元個数</th>
                    <th class="col-quantity">数量</th>
                    <th class="col-remarks">備考</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $row)
                <tr>
                    <td class="text-center">{{ $row->inbound_lot_number ?: '' }}</td>
                    <td class="text-left">{{ $row->product_name }}</td>
                    <td class="text-left">{{ $row->specifications }}</td>
                    <td class="text-center">{{ $row->original_quantity > 0 ? number_format($row->original_quantity) : '' }}</td>
                    <td class="text-center">{{ $row->quantity > 0 ? number_format($row->quantity) : '' }}</td>
                    <td class="text-center">{{ $row->notes ?: '' }}</td>
                </tr>
                @endforeach

                <!-- 添加空行以匹配图片布局 -->
                @for($i = count($rows); $i < 3; $i++)
                <tr class="empty-row">
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
                    <td></td>
                    <td class="text-center">
                        @if($totals['quantity'] > 0)
                            {{ number_format($totals['quantity']) }}
                        @endif
                    </td>
                    <td></td>
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
