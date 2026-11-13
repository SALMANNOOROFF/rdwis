<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Allocations and Subhead Statuses</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <style>
        * { box-sizing: border-box; }
        body { font-family: "Segoe UI", Arial, sans-serif; color: #111; margin: 0; padding: 18px 20px; background: #f6f7f9; }
        .sheet { background: #fff; padding: 18px 20px; border: 1px solid #e2e8f0; border-radius: 8px; }
        h1 { font-size: 18px; margin: 0 0 2px; letter-spacing: .5px; }
        .sub { font-size: 12px; color: #64748b; margin-bottom: 14px; }
        .bar { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between;
               background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 12px; margin-bottom: 14px; }
        .bar .grp { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 12px; }
        .bar label { font-size: 12px; margin: 0; cursor: pointer; }
        .btn { border: 1px solid #cbd5e1; background: #fff; border-radius: 5px; padding: 4px 12px; font-size: 12px; cursor: pointer; }
        .btn.on { background: #2563eb; border-color: #2563eb; color: #fff; font-weight: 700; }
        .btn.print { background: #0f172a; border-color: #0f172a; color: #fff; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; font-size: 11.5px; }
        th, td { border: 1px solid #cbd5e1; padding: 4px 7px; }
        thead th { background: #e2e8f0; font-size: 10.5px; text-transform: uppercase; letter-spacing: .3px; text-align: center; }
        thead tr.g th { background: #cbd5e1; }
        td.num, th.num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
        td.txt { text-align: left; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tfoot td { background: #e2e8f0; font-weight: 700; }
        .hidecol { display: none !important; }
        .chooser { position: relative; }
        .chooser .menu { display: none; position: absolute; right: 0; top: 28px; z-index: 50; background: #fff; border: 1px solid #cbd5e1;
                         border-radius: 6px; padding: 8px 12px; min-width: 230px; max-height: 320px; overflow: auto; box-shadow: 0 6px 20px rgba(0,0,0,.15); }
        .chooser.open .menu { display: block; }
        .chooser .menu div { padding: 2px 0; font-size: 12px; }
        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            body { background: #fff; padding: 0; }
            .noprint { display: none !important; }
            .sheet { border: 0; padding: 0; }
            table { font-size: 9px; }
            thead { display: table-header-group; }
        }
    </style>
</head>
<body>
<div class="sheet">
    <h1>ALLOCATIONS AND SUBHEAD STATUSES</h1>
    <div class="sub">
        {{ $unitName }} &middot; {{ count($rows) }} project(s) &middot;
        Generated {{ $generatedAt->format('d M, Y H:i') }} &middot;
        <span id="unitNote">Figures in Rupees</span>
    </div>

    <div class="bar noprint">
        <div class="grp">
            <strong>Units:</strong>
            <button type="button" class="btn on" id="btnRs" onclick="setUnit('rs')">Rupees</button>
            <button type="button" class="btn" id="btnM" onclick="setUnit('m')">Millions</button>
        </div>
        <div class="grp">
            <div class="chooser" id="chooser">
                <button type="button" class="btn" onclick="document.getElementById('chooser').classList.toggle('open')">
                    <i class="fas fa-columns"></i> Columns
                </button>
                <div class="menu">
                    <div><label><input type="checkbox" class="colchk" value="c-alloc" checked> Allocation</label></div>
                    <div><label><input type="checkbox" class="colchk" value="c-mtss" checked> MTSS Share</label></div>
                    <div><label><input type="checkbox" class="colchk" value="c-nrdi" checked> NRDI Share</label></div>
                    <hr style="margin:6px 0;">
                    @foreach($subheadNames as $i => $name)
                        <div><label><input type="checkbox" class="colchk" value="c-sh{{ $i }}" checked> {{ $name }}</label></div>
                    @endforeach
                    <hr style="margin:6px 0;">
                    <div><label><input type="checkbox" class="colchk" value="c-total" checked> Total Balance</label></div>
                </div>
            </div>
            <button type="button" class="btn print" onclick="window.print()"><i class="fas fa-print"></i> Print / PDF</button>
            <button type="button" class="btn" onclick="window.close()">Close</button>
        </div>
    </div>

    <table>
        <thead>
            <tr class="g">
                <th rowspan="2" style="width: 34px;">#</th>
                <th rowspan="2" class="txt" style="min-width: 110px;">Division</th>
                <th rowspan="2" class="txt" style="min-width: 220px;">Project</th>
                <th rowspan="2" class="num c-alloc">Allocation</th>
                <th rowspan="2" class="num c-mtss">MTSS Share</th>
                <th rowspan="2" class="num c-nrdi">NRDI Share</th>
                @foreach($subheadNames as $i => $name)
                    <th colspan="3" class="c-sh{{ $i }}">{{ $name }}</th>
                @endforeach
                <th rowspan="2" class="num c-total">Total Balance</th>
            </tr>
            <tr>
                @foreach($subheadNames as $i => $name)
                    <th class="num c-sh{{ $i }}">Allocated</th>
                    <th class="num c-sh{{ $i }}">Expenditure</th>
                    <th class="num c-sh{{ $i }}">Balance</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $n => $r)
                <tr>
                    <td class="num">{{ $n + 1 }}</td>
                    <td class="txt">{{ $r['division'] }}</td>
                    <td class="txt"><strong>{{ $r['code'] }}</strong> &mdash; {{ $r['project'] }}</td>
                    <td class="num c-alloc money" data-raw="{{ $r['allocation'] }}">{{ number_format($r['allocation']) }}</td>
                    <td class="num c-mtss money" data-raw="{{ $r['mtss'] }}">{{ number_format($r['mtss']) }}</td>
                    <td class="num c-nrdi money" data-raw="{{ $r['nrdi'] }}">{{ number_format($r['nrdi']) }}</td>
                    @foreach($subheadNames as $i => $name)
                        @php $s = $r['subheads'][$name] ?? ['allocated' => 0, 'expenditure' => 0, 'balance' => 0]; @endphp
                        <td class="num c-sh{{ $i }} money" data-raw="{{ $s['allocated'] }}">{{ number_format($s['allocated']) }}</td>
                        <td class="num c-sh{{ $i }} money" data-raw="{{ $s['expenditure'] }}">{{ number_format($s['expenditure']) }}</td>
                        <td class="num c-sh{{ $i }} money" data-raw="{{ $s['balance'] }}">{{ number_format($s['balance']) }}</td>
                    @endforeach
                    <td class="num c-total money" data-raw="{{ $r['balance'] }}"><strong>{{ number_format($r['balance']) }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="99" style="text-align:center; padding: 20px; color:#64748b;">No accounts found for the selected filters.</td></tr>
            @endforelse
        </tbody>
        @if(count($rows))
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right;">TOTAL</td>
                <td class="num c-alloc money" data-raw="{{ $totals['allocation'] }}">{{ number_format($totals['allocation']) }}</td>
                <td class="num c-mtss money" data-raw="{{ $totals['mtss'] }}">{{ number_format($totals['mtss']) }}</td>
                <td class="num c-nrdi money" data-raw="{{ $totals['nrdi'] }}">{{ number_format($totals['nrdi']) }}</td>
                @foreach($subheadNames as $i => $name)
                    @php $t = $totals['subheads'][$name]; @endphp
                    <td class="num c-sh{{ $i }} money" data-raw="{{ $t['allocated'] }}">{{ number_format($t['allocated']) }}</td>
                    <td class="num c-sh{{ $i }} money" data-raw="{{ $t['expenditure'] }}">{{ number_format($t['expenditure']) }}</td>
                    <td class="num c-sh{{ $i }} money" data-raw="{{ $t['balance'] }}">{{ number_format($t['balance']) }}</td>
                @endforeach
                <td class="num c-total money" data-raw="{{ $totals['balance'] }}">{{ number_format($totals['balance']) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>
</div>

<script>
function setUnit(mode) {
    const inM = (mode === 'm');
    document.getElementById('btnM').classList.toggle('on', inM);
    document.getElementById('btnRs').classList.toggle('on', !inM);
    document.getElementById('unitNote').innerText = inM ? 'Figures in Millions of Rs' : 'Figures in Rupees';
    document.querySelectorAll('td.money').forEach(function (td) {
        const raw = parseFloat(td.getAttribute('data-raw') || 0) || 0;
        const val = inM ? (raw / 1000000) : raw;
        const txt = inM ? val.toFixed(2) : Math.round(val).toLocaleString('en-US');
        if (td.querySelector('strong')) { td.querySelector('strong').innerText = txt; } else { td.innerText = txt; }
    });
}

document.querySelectorAll('.colchk').forEach(function (chk) {
    chk.addEventListener('change', function () {
        document.querySelectorAll('.' + chk.value).forEach(function (cell) {
            cell.classList.toggle('hidecol', !chk.checked);
        });
    });
});

document.addEventListener('click', function (e) {
    const ch = document.getElementById('chooser');
    if (ch && !ch.contains(e.target)) { ch.classList.remove('open'); }
});
</script>
</body>
</html>
