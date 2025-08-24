<!doctype html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>PDF</title>
    <style>
        @page { margin: 16mm 9mm 14mm 9mm; }
        body {
            font-family: "Noto Sans JP", sans-serif;
            font-size: 11px;
        }
        .page-break {
            page-break-before: always;
        }
        .title { text-align:center; font-weight:700; font-size:16px; letter-spacing:1px; }
        table { width:100%; border-collapse:collapse; }
        th, td {
            border:0.4pt solid #000;
            padding:1px 2px;
            vertical-align:middle;
            line-height: 0.9;
        }
        th {
            text-align:center;
            font-weight:700;
            height: 24px;
            vertical-align: top;
            line-height: 0.85;
            padding-top: 1px;
            padding-bottom: 1px;
        }
        tr { page-break-inside: avoid; }

        /* 列宽（根据样张调过的近似比例，可再微调） */
        .w-no{width:8%}
        .w-date{width:6%}
        .w-name{width:32%}
        .w-unit{width:4%}
        .w-rec{width:6%}
        .w-stock{width:6%}
        .w-weight{width:7%}
        .w-dates{width:8%}
        .w-misc{width:23%}


        /* 上下两行的单元：让内文紧凑些 */
        .cell-2lines {
            line-height: 0.85;
            padding-top: 1px;
            padding-bottom: 1px;
        }
        .cell-2lines br {
            line-height: 0.6;
            margin: -1px 0;
        }
        .small {
            font-size: 9px;
            line-height: 0.85;
            margin: 0;
            padding: 0;
        }

        /* 嵌套表格样式 */
        .nested-table {
            width: 100%;
            border: none;
            margin: 0;
            padding: 0;
        }
        .nested-table td {
            border: none;
            padding: 0;
            line-height: 0.85;
            min-height: 12px;
            vertical-align: top;
        }
        .nested-left {
            text-align: left;
            width: 50%;
        }
        .nested-right {
            text-align: right;
            width: 50%;
        }
        .nested-small {
            font-size: 9px;
        }

        /* 右对齐样式 */
        .text-right {
            text-align: right;
        }

        /* 报告头部样式 */
        .header-main {
            display: table;
            width: 100%;
            margin-bottom: 8mm;
        }
        .header-left {
            display: table-cell;
            width: 28%;
            vertical-align: top;
        }
        .header-center {
            display: table-cell;
            width: 47%;
            vertical-align: middle;
            text-align: center;
            padding: 0 3mm;
        }
        .header-right {
            display: table-cell;
            width: 25%;
            vertical-align: top;
            text-align: right;
        }
        .sender-box {
            border: 1pt solid #000;
            padding: 2mm;
            font-size: 8px;
            height: 22mm;
            line-height: 1.0;
            overflow: hidden;
            word-wrap: break-word;
        }
        .report-title {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
            border-bottom: 2pt solid #000;
            display: inline-block;
            padding-bottom: 2mm;
            margin-bottom: 3mm;
        }
        .report-date {
            font-size: 12px;
            margin-bottom: 3mm;
        }
        .report-description {
            font-size: 10px;
            line-height: 1.1;
        }
        .company-info {
            font-size: 10px;
            line-height: 1.2;
        }
        .stamp-box {
            border: 1pt solid #000;
            width: 25mm;
            height: 15mm;
            margin-top: 2mm;
            font-size: 9px;
            text-align: center;
            margin-left: auto;
            position: relative;
        }
        .stamp-header {
            border-bottom: 1pt solid #000;
            padding: 1mm 0;
            font-size: 8px;
            line-height: 1.0;
        }
        .stamp-area {
            height: 10mm;
            width: 100%;
        }
        .report-number {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3mm;
        }
        .description {
            text-align: center;
            font-size: 11px;
            margin: 4mm 0;
        }
    </style>
</head>
<body>

@foreach($pages as $pageIndex => $pageRows)
@if($pageIndex > 0)
<div class="page-break"></div>
@endif

<!-- 报告头主体 - 三列布局 -->
<div class="header-main">
    <!-- 左侧：客户信息 -->
    <div class="header-left">
        <div class="sender-box">
            @if($issuer['zip'])
                〒{{ $issuer['zip'] }}<br>
            @endif
            {{ $issuer['address'] ?? '' }}<br>
            <br>
            {{ $issuer['name'] }} 殿<br>
            <br>
            FAX: {{ $issuer['fax'] }}
        </div>
    </div>

    <!-- 中间：报告标题和日期 -->
    <div class="header-center">
        <div class="report-title">在 庫 報 告 書</div>
        <div class="report-date">{{ $reportDate ?? now()->format('Y年n月j日') }} 作成</div>
        <div class="report-description">
            {{ now()->format('Y年n月j日') }}現在下記の通り在庫していることを報告致します。
        </div>
    </div>

    <!-- 右侧：仓库信息 -->
    <div class="header-right">
        <div class="report-number">No. {{ $pageIndex + 1 }}-{{ $totalPages }}</div>
        <div class="company-info">
            {{ $company['name'] }}<br>
            {{ $company['address'] }}<br>
            <br>
            TEL: {{ $company['tel'] }}<br>
            FAX: {{ $company['fax']  }}<br>
            <div class="stamp-box">
                <div class="stamp-header">担 当 印</div>
                <div class="stamp-area"></div>
            </div>
        </div>
    </div>
</div>



<table style="margin-top:6mm">
    <thead>
    <tr>
        <th class="w-no">入庫NO<br><span>（商品コード）</span></th>
        <th class="w-date">入庫日<br><span>&nbsp;</span></th>
        <th class="w-name">
            <table class="nested-table">
                <tr>
                    <td class="nested-left">品名</td>
                    <td class="nested-left">荷印</td>
                </tr>
                <tr>
                    <td class="nested-left">規格</td>
                    <td class="nested-right">定・不</td>
                </tr>
            </table>
        </th>
        <th class="w-unit">単 量<br><span>単 位</span></th>
        <th class="w-rec">入庫元数<br><span>&nbsp;</span></th>
        <th class="w-stock">在 庫 数<br><span>&nbsp;</span></th>
        <th class="w-weight">在庫重量<br><span>&nbsp;</span></th>
        <th class="w-dates">製造日<br><span>賞味期限</span></th>
        <th class="w-misc">
            <table class="nested-table">
                <tr>
                    <td class="nested-left">契約NO</td>
                    <td class="nested-left">船 名</td>
                </tr>
                <tr>
                    <td class="nested-left">再保先名</td>
                    <td class="nested-right">再保先NO</td>
                </tr>
            </table>
        </th>
    </tr>
    </thead>
    <tbody>
    @foreach($pageRows as $row)
        <tr>
            <td class="cell-2lines">
                {{ $row->receipt_no }}
                <br>
                ({{ $row->product_sku }})
            </td>

            <td class="cell-2lines">
                @if($row->inbound_date)
                    {{ $row->inbound_date->format('Y/m/d') }}
                @else
                    -
                @endif
                <br>
                {!! '&nbsp;' !!}
            </td>

            <td class="cell-2lines">
                <table class="nested-table">
                    <tr>
                        <td class="nested-left">{!! $row->product_name ?? '&nbsp;' !!}</td>
                        <td class="nested-left">{!! $row->cargo_mark ?? '&nbsp;' !!}</td>
                    </tr>
                    <tr>
                        <td class="nested-left nested-small">{!! $row->pack_spec_text ?? '&nbsp;' !!}</td>
                        <td class="nested-right nested-small">
                            {{ $row->is_fixed_weight ? '定貫' : '不定貫' }}
                        </td>
                    </tr>
                </table>
            </td>

            <td class="cell-2lines text-right">
                {{ number_format($row->unit_weight, 2) }}
                <br>
                <span class="small">
                    {{ $row->weight_unit }}
                </span>
            </td>

            <td class="cell-2lines text-right">
                {{ number_format($row->inbound_quantity) }}
                <br>
                {!! '&nbsp;' !!}
            </td>

            <td class="cell-2lines text-right">
                {{ number_format($row->left_quantity) }}
                <br>
                @if($row->left_sub_quantity > 0)
                    <span class="small">{{ number_format($row->left_sub_quantity) }}/{{ number_format($row->units_per_case) }}</span>
                @else
                    {!! '&nbsp;' !!}
                @endif
            </td>

            <td class="cell-2lines text-right">
                {{ number_format($row->onhand_weight, 2) }}
                <br>
                {!! '&nbsp;' !!}
            </td>

            <td class="cell-2lines">
                @if($row->manufactured_date)
                    {{ $row->manufactured_date->format('Y/m/d') }}
                @else
                    -
                @endif
                <br>
                @if($row->best_before_date)
                    {{ $row->best_before_date->format('Y/m/d') }}
                @else
                    -
                @endif
            </td>

            <td class="cell-2lines">
                <table class="nested-table">
                    <tr>
                        <td class="nested-left">{!! $row->contract_no ?? '&nbsp;' !!}</td>
                        <td class="nested-left">{!! $row->vessel_name ?? '&nbsp;' !!}</td>
                    </tr>
                    <tr>
                        <td class="nested-left nested-small">{!! $row->client_name ?? '&nbsp;' !!}</td>
                        <td class="nested-right nested-small">{!! $row->client_code ?? '&nbsp;' !!}</td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach

    @if($pageIndex == $totalPages - 1)
    <!-- 合计行只在最后一页显示 -->
    <tr>
        <td colspan="5" style="text-align: center; font-weight: bold; padding: 3px;">
            ★合 計★
        </td>
        <td colspan="2" style="text-align: center; font-weight: normal; padding: 3px;">
            {{ number_format($totals['left_quantity']) }}
        </td>
        <td colspan="2" style="border: 0.4pt solid #000;"></td>
    </tr>
    @endif

    </tbody>
</table>

@endforeach

</body>
</html>
