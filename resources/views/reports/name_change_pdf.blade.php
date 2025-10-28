<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>名义变更报告</title>
    <style>
        body {
            font-family: 'SimSun', serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 16px;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 25px;
        }
        
        .info-row {
            display: flex;
            margin-bottom: 8px;
        }
        
        .info-label {
            width: 120px;
            font-weight: bold;
        }
        
        .info-value {
            flex: 1;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th,
        .table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        
        .table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        
        .table td {
            text-align: center;
        }
        
        .footer {
            margin-top: 40px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 200px;
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #333;
            height: 40px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">名义变更报告</div>
        <div class="subtitle">Name Change Report</div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">报告编号：</div>
            <div class="info-value">{{ $report->id }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">名义变更单号：</div>
            <div class="info-value">{{ $nameChange->name_change_order_id ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">名义变更日期：</div>
            <div class="info-value">{{ $nameChange->name_change_date }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">仓库：</div>
            <div class="info-value">{{ $warehouse->name ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">客户：</div>
            <div class="info-value">{{ $customer->name ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">承运商：</div>
            <div class="info-value">{{ $nameChange->carrier_name ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">状态：</div>
            <div class="info-value">{{ $nameChange->status }}</div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>序号</th>
                <th>商品名称</th>
                <th>批次号</th>
                <th>数量</th>
                <th>备注</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->product->name ?? 'N/A' }}</td>
                <td>{{ $item->lot_number ?? 'N/A' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ $item->note ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">
            <div>仓库管理员</div>
            <div class="signature-line"></div>
            <div>签名：________________</div>
        </div>
        <div class="signature-box">
            <div>客户代表</div>
            <div class="signature-line"></div>
            <div>签名：________________</div>
        </div>
    </div>

    <div class="footer">
        <div>生成时间：{{ $generatedAt }}</div>
        <div>报告格式：{{ strtoupper($report->format) }}</div>
    </div>
</body>
</html>
