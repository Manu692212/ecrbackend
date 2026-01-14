<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New Application Received</title>
    <style>
        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }
        .wrapper {
            max-width: 640px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.1);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 999px;
            background: #eef2ff;
            color: #4338ca;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2em;
        }
        .section {
            margin-top: 24px;
            padding: 20px;
            border-radius: 16px;
            background: #f8fafc;
        }
        .section h3 {
            margin-top: 0;
            font-size: 16px;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .section p {
            margin: 6px 0;
        }
        .table {
            margin-top: 12px;
        }
        .table-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .table-row:last-child {
            border-bottom: 0;
        }
        .table-label {
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <span class="badge">
            <span style="width:8px;height:8px;border-radius:50%;background:#4338ca;"></span>
            New Submission
        </span>
        <h1 style="margin:24px 0 8px;font-size:28px;">{{ $submission->full_name }}</h1>
        <p style="margin:0;color:#64748b;">{{ ucfirst($submission->form_type) }} · {{ $submission->created_at->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</p>

        <div class="section">
            <h3>Contact Details</h3>
            <div class="table">
                <div class="table-row">
                    <span class="table-label">Email</span>
                    <span>{{ $submission->email }}</span>
                </div>
                @if ($submission->phone)
                    <div class="table-row">
                        <span class="table-label">Phone</span>
                        <span>{{ $submission->phone }}</span>
                    </div>
                @endif
                @if ($submission->title)
                    <div class="table-row">
                        <span class="table-label">Title / Course</span>
                        <span>{{ $submission->title }}</span>
                    </div>
                @endif
            </div>
        </div>

        @if (!empty($payload))
            <div class="section">
                <h3>Submission Data</h3>
                <div class="table">
                    @foreach($payload as $key => $value)
                        <div class="table-row">
                            <span class="table-label">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                            <span>{{ is_array($value) ? json_encode($value) : $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <p style="margin-top:32px;font-size:12px;color:#94a3b8;text-align:center;">
            This notification was sent automatically from the ECR backend.
        </p>
    </div>
</body>
</html>
