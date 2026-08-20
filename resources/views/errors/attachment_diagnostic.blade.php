<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Attachment Diagnostics | RDWIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Inter:wght@400;500;600;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --rd-bg: #090e1a;
            --rd-surface: #10182b;
            --rd-surface2: #19253d;
            --rd-border: rgba(255,255,255,0.08);
            --rd-accent: #f39c12;
            --rd-danger: #e74c3c;
            --rd-success: #2ecc71;
            --rd-info: #00b4d8;
        }
        body {
            margin: 0;
            padding: 24px;
            background: var(--rd-bg);
            color: #e2e8f0;
            font-family: 'Inter', sans-serif;
            font-size: 13px;
        }
        .diag-card {
            max-width: 900px;
            margin: 0 auto;
            background: var(--rd-surface);
            border: 1px solid var(--rd-border);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        .diag-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--rd-border);
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .diag-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 22px;
            font-weight: 700;
            color: var(--rd-accent);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .diag-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }
        .diag-box {
            background: var(--rd-surface2);
            border: 1px solid var(--rd-border);
            border-radius: 8px;
            padding: 14px;
        }
        .diag-box-title {
            font-family: 'Rajdhani', sans-serif;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--rd-info);
            margin-bottom: 10px;
        }
        .diag-meta-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px solid rgba(255,255,255,0.04);
        }
        .diag-meta-label { color: #94a3b8; }
        .diag-meta-val { font-weight: 600; color: #fff; font-family: 'Fira Code', monospace; }
        .path-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .path-item {
            background: #0b1120;
            border: 1px solid var(--rd-border);
            border-radius: 6px;
            padding: 8px 12px;
            font-family: 'Fira Code', monospace;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            word-break: break-all;
        }
        .badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            white-space: nowrap;
            margin-left: 10px;
        }
        .badge-success { background: rgba(46, 204, 113, 0.15); color: var(--rd-success); border: 1px solid rgba(46, 204, 113, 0.3); }
        .badge-danger { background: rgba(231, 76, 60, 0.15); color: var(--rd-danger); border: 1px solid rgba(231, 76, 60, 0.3); }
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: var(--rd-accent);
            color: #000;
            font-weight: 700;
            border-radius: 6px;
            text-decoration: none;
            font-family: 'Rajdhani', sans-serif;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="diag-card">
        <div class="diag-header">
            <div class="diag-title">
                <i class="fas fa-stethoscope"></i> Quote Attachment Diagnostics Report
            </div>
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-arrow-left"></i> Go Back
            </a>
        </div>

        <div class="diag-grid">
            <div class="diag-box">
                <div class="diag-box-title"><i class="fas fa-network-wired mr-1"></i> Client Network Information</div>
                <div class="diag-meta-row">
                    <span class="diag-meta-label">Client IP:</span>
                    <span class="diag-meta-val">{{ $diagnostics['client_ip'] ?? 'Unknown' }}</span>
                </div>
                <div class="diag-meta-row">
                    <span class="diag-meta-label">User Account:</span>
                    <span class="diag-meta-val">{{ ($diagnostics['user']['name'] ?? 'N/A') . ' (' . ($diagnostics['user']['area'] ?? 'N/A') . ')' }}</span>
                </div>
                <div class="diag-meta-row">
                    <span class="diag-meta-label">Timestamp:</span>
                    <span class="diag-meta-val">{{ $diagnostics['timestamp'] ?? now() }}</span>
                </div>
            </div>

            <div class="diag-box">
                <div class="diag-box-title"><i class="fas fa-database mr-1"></i> Database Record (purattachments)</div>
                <div class="diag-meta-row">
                    <span class="diag-meta-label">Target ID:</span>
                    <span class="diag-meta-val">{{ $id }}</span>
                </div>
                <div class="diag-meta-row">
                    <span class="diag-meta-label">DB Record Status:</span>
                    <span class="diag-meta-val">
                        @if($diagnostics['attachment_record'])
                            <span class="badge badge-success">Found (ID: {{ $diagnostics['attachment_record']->pat_id }})</span>
                        @else
                            <span class="badge badge-danger">Not Found in DB</span>
                        @endif
                    </span>
                </div>
                <div class="diag-meta-row">
                    <span class="diag-meta-label">DB Stored Path:</span>
                    <span class="diag-meta-val">{{ $diagnostics['db_path'] ?: 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="diag-box" style="margin-bottom: 20px;">
            <div class="diag-box-title"><i class="fas fa-search-location mr-1"></i> Physical Disk Path Verification</div>
            <ul class="path-list">
                @forelse($diagnostics['tested_paths'] as $tp)
                    <li class="path-item">
                        <span>{{ $tp['path'] }}</span>
                        @if($tp['exists'])
                            <span class="badge badge-success"><i class="fas fa-check"></i> Found ({{ number_format($tp['size']) }} bytes)</span>
                        @else
                            <span class="badge badge-danger"><i class="fas fa-times"></i> Missing</span>
                        @endif
                    </li>
                @empty
                    <li class="path-item text-muted">No paths tested because no file path was found in database for this ID.</li>
                @endforelse
            </ul>
        </div>

        @if(!$diagnostics['file_found'])
        <div style="background: rgba(231, 76, 60, 0.1); border: 1px solid rgba(231, 76, 60, 0.3); padding: 14px; border-radius: 8px; color: #fca5a5;">
            <strong style="display: block; margin-bottom: 4px;"><i class="fas fa-exclamation-triangle mr-1"></i> Root Cause & Suggested Action:</strong>
            1. The physical file was not found at any standard storage location. Please re-upload the quotation document on the case page.<br>
            2. If you are uploading from another PC, ensure storage symlinks and NTFS permissions permit PHP to save files in <code>storage/app/public/purchase/quotes</code>.
        </div>
        @else
        <div style="background: rgba(46, 204, 113, 0.1); border: 1px solid rgba(46, 204, 113, 0.3); padding: 14px; border-radius: 8px; color: #86efac;">
            <strong><i class="fas fa-check-circle mr-1"></i> File Verified on Disk:</strong> <code>{{ $diagnostics['matched_path'] }}</code>
        </div>
        @endif
    </div>
</body>
</html>
