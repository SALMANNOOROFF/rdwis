<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Summary - {{ $project->prj_code }} - {{ $project->prj_title }}</title>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            padding: 0;
            background: #cbd5e1;
            font-family: 'Calibri', 'Segoe UI', Arial, sans-serif;
            color: #000;
        }

        /* Top Action Bar for screen view */
        .top-action-bar {
            position: sticky;
            top: 0;
            left: 0;
            right: 0;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.25);
            z-index: 9999;
        }
        .top-action-bar .brand {
            font-family: 'Rajdhani', sans-serif;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .top-action-bar .actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            font-weight: 700;
            padding: 7px 18px;
            border-radius: 9999px;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.15s ease;
        }
        .btn-print {
            background: #2563eb;
            color: #ffffff;
        }
        .btn-print:hover {
            background: #1d4ed8;
        }
        .btn-close {
            background: #334155;
            color: #f8fafc;
        }
        .btn-close:hover {
            background: #475569;
        }

        /* Paper Container */
        .paper-wrapper {
            padding: 24px 12px 40px;
            display: flex;
            justify-content: center;
        }
        .paper-sheet {
            background: #ffffff;
            width: 210mm;
            min-height: 297mm;
            padding: 12mm 15mm;
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
            border-radius: 4px;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
            body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .top-action-bar {
                display: none !important;
            }
            .paper-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }
            .paper-sheet {
                width: 100% !important;
                min-height: auto !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</head>
<body>

    {{-- Screen Floating Toolbar --}}
    <div class="top-action-bar">
        <div class="brand">
            <i class="fas fa-file-invoice-dollar" style="color: #38bdf8;"></i>
            <span>Official Financial Account Summary &mdash; {{ $project->prj_code }}</span>
        </div>
        <div class="actions">
            <button type="button" class="btn-action btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Print Document
            </button>
            <button type="button" class="btn-action btn-close" onclick="window.close()">
                <i class="fas fa-times"></i> Close Window
            </button>
        </div>
    </div>

    {{-- Printable Paper Sheet --}}
    <div class="paper-wrapper">
        <div class="paper-sheet">
            @if($head)
                @include('projects.partials.printable_financial_summary')
            @else
                <div style="text-align: center; padding: 40px 20px; font-weight: bold; color: #b91c1c;">
                    <i class="fas fa-exclamation-triangle fa-2x" style="margin-bottom: 10px; display: block;"></i>
                    No financial head record linked to this project to generate the Account Summary.
                </div>
            @endif
        </div>
    </div>

    <script>
        // Automatically open print dialog once page is fully loaded
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
</body>
</html>
