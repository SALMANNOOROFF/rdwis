import { useEffect, useMemo, useRef, useState } from 'react';
import Chart from 'chart.js/auto';

function formatMoney(value) {
    const n = Number(value || 0);
    return n.toLocaleString(undefined, { maximumFractionDigits: 0 });
}

function formatMoney2(value) {
    const n = Number(value || 0);
    return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function cn(...parts) {
    return parts.filter(Boolean).join(' ');
}

function useChart(canvasRef, config) {
    const chartRef = useRef(null);

    useEffect(() => {
        const canvas = canvasRef.current;
        if (!canvas || !config) return;

        if (chartRef.current) {
            chartRef.current.destroy();
            chartRef.current = null;
        }

        chartRef.current = new Chart(canvas.getContext('2d'), config);

        return () => {
            if (chartRef.current) {
                chartRef.current.destroy();
                chartRef.current = null;
            }
        };
    }, [canvasRef, config]);
}

function Card({ title, value, sub, tone = 'cyan' }) {
    const toneClass =
        tone === 'green'
            ? 'ring-green-400/20 shadow-[0_0_0_1px_rgba(34,197,94,0.18),0_0_28px_rgba(34,197,94,0.12)]'
            : tone === 'amber'
              ? 'ring-amber-400/20 shadow-[0_0_0_1px_rgba(245,158,11,0.18),0_0_28px_rgba(245,158,11,0.10)]'
              : tone === 'red'
                ? 'ring-red-400/20 shadow-[0_0_0_1px_rgba(248,113,113,0.18),0_0_28px_rgba(248,113,113,0.10)]'
                : 'ring-cyan-400/20 shadow-[0_0_0_1px_rgba(0,191,255,0.18),0_0_28px_rgba(0,191,255,0.12)]';

    return (
        <div
            className={cn(
                'rounded-xl bg-[#121A22]/80 backdrop-blur border border-white/5 ring-1',
                toneClass,
                'px-3 py-2 hover:border-cyan-400/25 transition'
            )}
        >
            <div className="text-[11px] uppercase tracking-[0.16em] text-[#E5E5E5]/70">{title}</div>
            <div className="mt-1.5 text-[20px] font-semibold text-[#E5E5E5] leading-tight">{value}</div>
            {sub ? <div className="mt-1 text-xs text-[#E5E5E5]/60">{sub}</div> : null}
        </div>
    );
}

function Pill({ active, onClick, children, right = false }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={cn(
                'px-3 py-1.5 text-xs rounded-full border transition',
                right ? 'ml-auto' : '',
                active
                    ? 'border-cyan-300/70 text-cyan-200 shadow-[0_0_0_1px_rgba(0,191,255,0.2),0_0_18px_rgba(0,191,255,0.20)] bg-[#0A0F14]'
                    : 'border-white/10 text-[#E5E5E5]/70 hover:text-[#E5E5E5] hover:border-cyan-300/30'
            )}
        >
            {children}
        </button>
    );
}

function SectionTitle({ title, right }) {
    return (
        <div className="flex items-center justify-between gap-3">
            <div className="text-sm font-semibold text-[#E5E5E5]">{title}</div>
            {right ? <div className="text-xs text-[#E5E5E5]/55">{right}</div> : null}
        </div>
    );
}

function ChartCard({ title, right, children }) {
    return (
        <div className="rounded-xl bg-[#121A22]/80 backdrop-blur border border-white/5 px-4 py-3">
            <SectionTitle title={title} right={right} />
            <div className="mt-3">{children}</div>
        </div>
    );
}

function StatusBadge({ status }) {
    const s = String(status || '').toLowerCase();
    const cls =
        s.includes('complete') || s === 'closed'
            ? 'bg-green-500/15 text-green-300 border-green-500/20'
            : s.includes('delay')
              ? 'bg-red-500/15 text-red-300 border-red-500/20'
              : 'bg-cyan-500/12 text-cyan-200 border-cyan-500/20';

    return <span className={cn('px-2 py-0.5 text-[11px] rounded-full border', cls)}>{status}</span>;
}

function CodeChip({ code, status }) {
    const s = String(status || '').toLowerCase();
    const cls =
        s.includes('complete')
            ? 'border-green-400/30 text-green-200 bg-green-500/10'
            : s.includes('delay')
              ? 'border-red-400/30 text-red-200 bg-red-500/10'
              : 'border-cyan-300/30 text-cyan-200 bg-cyan-500/10';

    return (
        <span className={cn('px-2.5 py-1 text-[11px] rounded-full border whitespace-nowrap', cls)}>
            {code}
        </span>
    );
}

export default function NrdiCommandDashboard() {
    const [division, setDivision] = useState('all');
    const [projectId, setProjectId] = useState(null);
    const [mode, setMode] = useState('monthly');
    const [term, setTerm] = useState('');
    const [sort, setSort] = useState({ key: 'code', dir: 'asc' });
    const [loading, setLoading] = useState(true);
    const [data, setData] = useState(null);

    const financeCanvasRef = useRef(null);
    const purchasePerDivCanvasRef = useRef(null);
    const purchaseDonutCanvasRef = useRef(null);
    const employeesPerDivCanvasRef = useRef(null);
    const employeesDonutCanvasRef = useRef(null);
    const projectsPerDivCanvasRef = useRef(null);
    const employeesPerProjectCanvasRef = useRef(null);
    const projectsStatusCanvasRef = useRef(null);
    const projectProgressCanvasRef = useRef(null);
    const budgetVsUtilPerDivCanvasRef = useRef(null);

    async function load(nextDivision = division, nextProjectId = projectId) {
        setLoading(true);
        try {
            const qs = new URLSearchParams();
            qs.set('division', String(nextDivision));
            if (nextProjectId) qs.set('project_id', String(nextProjectId));
            const res = await fetch(`/nrdi/dashboard-data?${qs.toString()}`, {
                headers: { Accept: 'application/json' },
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const json = await res.json();
            setData(json);
        } finally {
            setLoading(false);
        }
    }

    useEffect(() => {
        load(division, projectId);
    }, [division, projectId]);

    const financeSeries = useMemo(() => {
        const series = data?.charts?.finance?.[mode] || [];
        return {
            labels: series.map((p) => p.period),
            budget: series.map((p) => Number(p.budget || 0)),
            utilized: series.map((p) => Number(p.utilized || 0)),
        };
    }, [data, mode]);

    const financeConfig = useMemo(() => {
        if (!financeSeries.labels.length) return null;
        return {
            type: 'bar',
            data: {
                labels: financeSeries.labels,
                datasets: [
                    {
                        type: 'line',
                        label: 'Budget',
                        data: financeSeries.budget,
                        borderColor: '#00BFFF',
                        backgroundColor: 'rgba(0,191,255,0.12)',
                        borderWidth: 2,
                        tension: 0.35,
                        pointRadius: 2,
                    },
                    {
                        label: 'Utilized',
                        data: financeSeries.utilized,
                        backgroundColor: 'rgba(229,229,229,0.12)',
                        borderColor: 'rgba(229,229,229,0.35)',
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: 'rgba(229,229,229,0.8)' } },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatMoney2(ctx.parsed.y)}`,
                        },
                    },
                },
                scales: {
                    x: {
                        ticks: { color: 'rgba(229,229,229,0.55)' },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                    y: {
                        ticks: { color: 'rgba(229,229,229,0.55)' },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                },
            },
        };
    }, [financeSeries]);

    const casesPerDiv = useMemo(() => data?.charts?.casesPerDivision || [], [data]);
    const purchaseDonut = useMemo(() => {
        return {
            reviewed: Number(data?.kpis?.reviewedCases || 0),
            pending: Number(data?.kpis?.pendingCases || 0),
        };
    }, [data]);

    const purchaseDonutConfig = useMemo(() => {
        if (!purchaseDonut.reviewed && !purchaseDonut.pending) return null;
        return {
            type: 'doughnut',
            data: {
                labels: ['Reviewed', 'Pending'],
                datasets: [
                    {
                        data: [purchaseDonut.reviewed, purchaseDonut.pending],
                        backgroundColor: ['rgba(34,197,94,0.75)', 'rgba(0,191,255,0.60)'],
                        borderColor: 'rgba(255,255,255,0.10)',
                        borderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: 'rgba(229,229,229,0.8)' } },
                },
            },
        };
    }, [purchaseDonut]);

    const casesPerDivConfig = useMemo(() => {
        if (!casesPerDiv.length) return null;
        return {
            type: 'bar',
            data: {
                labels: casesPerDiv.map((d) => d.division),
                datasets: [
                    {
                        label: 'Cases',
                        data: casesPerDiv.map((d) => Number(d.count || 0)),
                        backgroundColor: 'rgba(0,191,255,0.16)',
                        borderColor: 'rgba(0,191,255,0.55)',
                        borderWidth: 1,
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: (ctx) => `${ctx.parsed.y}` },
                    },
                },
                scales: {
                    x: {
                        ticks: { color: 'rgba(229,229,229,0.55)' },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                    y: {
                        ticks: { color: 'rgba(229,229,229,0.55)', precision: 0 },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                },
            },
        };
    }, [casesPerDiv]);

    const employeesPerDiv = useMemo(() => data?.charts?.employeesPerDivision || [], [data]);
    const employeesDonut = useMemo(() => {
        const total = Number(data?.kpis?.employeesTotal || 0);
        const hired = Number(data?.kpis?.employeesHired || 0);
        return { total, hired, existing: Math.max(0, total - hired) };
    }, [data]);

    const employeesDonutConfig = useMemo(() => {
        if (!employeesDonut.total) return null;
        return {
            type: 'doughnut',
            data: {
                labels: ['Hired (12m)', 'Existing'],
                datasets: [
                    {
                        data: [employeesDonut.hired, employeesDonut.existing],
                        backgroundColor: ['rgba(0,191,255,0.60)', 'rgba(229,229,229,0.12)'],
                        borderColor: 'rgba(255,255,255,0.10)',
                        borderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: 'rgba(229,229,229,0.8)' } },
                },
            },
        };
    }, [employeesDonut]);

    const employeesPerDivConfig = useMemo(() => {
        if (!employeesPerDiv.length) return null;
        return {
            type: 'bar',
            data: {
                labels: employeesPerDiv.map((d) => d.division),
                datasets: [
                    {
                        label: 'Employees',
                        data: employeesPerDiv.map((d) => Number(d.count || 0)),
                        backgroundColor: 'rgba(229,229,229,0.14)',
                        borderColor: 'rgba(229,229,229,0.30)',
                        borderWidth: 1,
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        ticks: { color: 'rgba(229,229,229,0.55)' },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                    y: {
                        ticks: { color: 'rgba(229,229,229,0.55)', precision: 0 },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                },
            },
        };
    }, [employeesPerDiv]);

    const projectsPerDiv = useMemo(() => data?.charts?.projectsPerDivision || [], [data]);
    const projectsPerDivConfig = useMemo(() => {
        if (!projectsPerDiv.length) return null;
        return {
            type: 'bar',
            data: {
                labels: projectsPerDiv.map((d) => d.division),
                datasets: [
                    {
                        label: 'Projects',
                        data: projectsPerDiv.map((d) => Number(d.count || 0)),
                        backgroundColor: 'rgba(0,191,255,0.16)',
                        borderColor: 'rgba(0,191,255,0.55)',
                        borderWidth: 1,
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        ticks: { color: 'rgba(229,229,229,0.55)' },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                    y: {
                        ticks: { color: 'rgba(229,229,229,0.55)', precision: 0 },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                },
            },
        };
    }, [projectsPerDiv]);

    const projectStatus = useMemo(() => data?.charts?.projectStatus || null, [data]);
    const projectStatusConfig = useMemo(() => {
        if (!projectStatus) return null;
        return {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'Ongoing', 'Delayed'],
                datasets: [
                    {
                        data: [
                            Number(projectStatus.completed || 0),
                            Number(projectStatus.ongoing || 0),
                            Number(projectStatus.delayed || 0),
                        ],
                        backgroundColor: ['rgba(34,197,94,0.75)', 'rgba(0,191,255,0.60)', 'rgba(248,113,113,0.70)'],
                        borderColor: 'rgba(255,255,255,0.10)',
                        borderWidth: 2,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { color: 'rgba(229,229,229,0.8)' } },
                },
            },
        };
    }, [projectStatus]);

    const projectProgress = useMemo(() => data?.charts?.projectProgress || [], [data]);
    const projectProgressConfig = useMemo(() => {
        if (!projectProgress.length) return null;
        return {
            type: 'bar',
            data: {
                labels: projectProgress.map((p) => p.code),
                datasets: [
                    {
                        label: '% Completion',
                        data: projectProgress.map((p) => Number(p.percent || 0)),
                        backgroundColor: 'rgba(0,191,255,0.16)',
                        borderColor: 'rgba(0,191,255,0.55)',
                        borderWidth: 1,
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        ticks: { color: 'rgba(229,229,229,0.55)' },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                    y: {
                        ticks: { color: 'rgba(229,229,229,0.55)', max: 100, beginAtZero: true },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                },
            },
        };
    }, [projectProgress]);

    const employeesPerProject = useMemo(() => data?.charts?.employeesPerProject || [], [data]);
    const employeesPerProjectConfig = useMemo(() => {
        if (!employeesPerProject.length) return null;
        return {
            type: 'bar',
            data: {
                labels: employeesPerProject.map((p) => p.code),
                datasets: [
                    {
                        label: 'Employees',
                        data: employeesPerProject.map((p) => Number(p.count || 0)),
                        backgroundColor: 'rgba(229,229,229,0.12)',
                        borderColor: 'rgba(229,229,229,0.30)',
                        borderWidth: 1,
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: {
                        ticks: { color: 'rgba(229,229,229,0.55)' },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                    y: {
                        ticks: { color: 'rgba(229,229,229,0.55)', precision: 0, beginAtZero: true },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                },
            },
        };
    }, [employeesPerProject]);

    const budgetVsUtil = useMemo(() => data?.charts?.budgetVsUtilPerDivision || [], [data]);
    const budgetVsUtilConfig = useMemo(() => {
        if (!budgetVsUtil.length) return null;
        return {
            type: 'bar',
            data: {
                labels: budgetVsUtil.map((d) => d.division),
                datasets: [
                    {
                        label: 'Budget',
                        data: budgetVsUtil.map((d) => Number(d.budget || 0)),
                        backgroundColor: 'rgba(0,191,255,0.16)',
                        borderColor: 'rgba(0,191,255,0.55)',
                        borderWidth: 1,
                        borderRadius: 8,
                    },
                    {
                        label: 'Utilized',
                        data: budgetVsUtil.map((d) => Number(d.utilized || 0)),
                        backgroundColor: 'rgba(229,229,229,0.10)',
                        borderColor: 'rgba(229,229,229,0.25)',
                        borderWidth: 1,
                        borderRadius: 8,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { color: 'rgba(229,229,229,0.8)' } } },
                scales: {
                    x: {
                        ticks: { color: 'rgba(229,229,229,0.55)' },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                    y: {
                        ticks: { color: 'rgba(229,229,229,0.55)' },
                        grid: { color: 'rgba(255,255,255,0.06)' },
                    },
                },
            },
        };
    }, [budgetVsUtil]);

    useChart(financeCanvasRef, financeConfig);
    useChart(purchaseDonutCanvasRef, purchaseDonutConfig);
    useChart(purchasePerDivCanvasRef, casesPerDivConfig);
    useChart(employeesPerDivCanvasRef, employeesPerDivConfig);
    useChart(employeesDonutCanvasRef, employeesDonutConfig);
    useChart(projectsPerDivCanvasRef, projectsPerDivConfig);
    useChart(employeesPerProjectCanvasRef, employeesPerProjectConfig);
    useChart(projectsStatusCanvasRef, projectStatusConfig);
    useChart(projectProgressCanvasRef, projectProgressConfig);
    useChart(budgetVsUtilPerDivCanvasRef, budgetVsUtilConfig);

    const divisions = data?.divisions || [];
    const kpis = data?.kpis || {};
    const tableProjects = data?.table?.projects || [];
    const codesByDivision = data?.lists?.codesByDivision || {};

    const filteredProjects = useMemo(() => {
        const q = term.trim().toLowerCase();
        let rows = q
            ? tableProjects.filter((p) => {
                  const t = `${p.code} ${p.name} ${p.division}`.toLowerCase();
                  return t.includes(q);
              })
            : tableProjects.slice();

        const dir = sort.dir === 'desc' ? -1 : 1;
        rows.sort((a, b) => {
            const ka = a[sort.key];
            const kb = b[sort.key];
            if (typeof ka === 'number' && typeof kb === 'number') return (ka - kb) * dir;
            return String(ka ?? '').localeCompare(String(kb ?? '')) * dir;
        });

        return rows;
    }, [tableProjects, term, sort]);

    function toggleSort(key) {
        setSort((prev) => {
            if (prev.key !== key) return { key, dir: 'asc' };
            return { key, dir: prev.dir === 'asc' ? 'desc' : 'asc' };
        });
    }

    const showAllComparisons = division === 'all' && !projectId;

    const selectedDivisionLabel = useMemo(() => {
        if (division === 'all') return 'All Divisions';
        const hit = divisions.find((d) => String(d.id) === String(division));
        return hit ? hit.label : 'Division';
    }, [division, divisions]);

    const selectedDivisionKey = useMemo(() => {
        if (division === 'all') return null;
        const hit = divisions.find((d) => String(d.id) === String(division));
        return hit?.key ? String(hit.key) : null;
    }, [division, divisions]);

    const codeStrip = useMemo(() => {
        if (division === 'all') return [];
        const list = (selectedDivisionKey && codesByDivision[selectedDivisionKey]) || [];
        return Array.isArray(list) ? list : [];
    }, [division, selectedDivisionKey, codesByDivision]);

    return (
        <div className="text-[#E5E5E5]">
            <div className="px-4 pt-4 pb-5 bg-[#0A0F14] rounded-xl border border-white/5 shadow-[0_0_0_1px_rgba(0,191,255,0.06)]">
                <div className="flex items-center justify-between gap-4">
                    <div>
                        <div className="text-lg font-semibold">Command Dashboard</div>
                    </div>
                    <div className="text-xs text-[#E5E5E5]/55">
                        {loading ? 'Updating…' : 'Live view'}
                    </div>
                </div>

                <div className="mt-4 rounded-xl bg-[#121A22]/80 backdrop-blur text-[#E5E5E5] border border-white/5 px-3 py-2">
                    <div className="flex flex-wrap items-center gap-2">
                        {divisions.map((d) => (
                            <Pill
                                key={d.id}
                                active={String(division) === String(d.id)}
                                onClick={() => {
                                    setProjectId(null);
                                    setDivision(String(d.id));
                                }}
                            >
                                {d.label}
                            </Pill>
                        ))}
                        <Pill
                            active={division === 'all'}
                            right
                            onClick={() => {
                                setProjectId(null);
                                setDivision('all');
                            }}
                        >
                            All
                        </Pill>
                    </div>
                </div>

                {division !== 'all' ? (
                    <div className="mt-3 rounded-xl bg-[#121A22]/80 backdrop-blur border border-white/5 px-4 py-3">
                        <SectionTitle
                            title={`${selectedDivisionLabel} Projects`}
                            right={
                                projectId
                                    ? `Project filter: ${data?.selectedProject?.code ?? ''}`
                                    : `${codeStrip.length} active`
                            }
                        />
                        <div className="mt-3 overflow-auto">
                            <div className="flex flex-nowrap gap-2">
                                {codeStrip.map((p) => (
                                    <button
                                        key={p.code}
                                        type="button"
                                        onClick={() => setProjectId(p.id)}
                                        className={cn(
                                            'hover:opacity-90 transition',
                                            String(projectId) === String(p.id) ? 'opacity-100' : 'opacity-85'
                                        )}
                                        title="Filter dashboard by this project"
                                    >
                                        <CodeChip code={p.code} status={p.status} />
                                    </button>
                                ))}
                                {projectId ? (
                                    <button
                                        type="button"
                                        onClick={() => setProjectId(null)}
                                        className="px-2.5 py-1 text-[11px] rounded-full border border-white/10 text-[#E5E5E5]/70 hover:text-[#E5E5E5] hover:border-cyan-300/30 transition whitespace-nowrap"
                                        title="Clear project filter"
                                    >
                                        Clear
                                    </button>
                                ) : null}
                                {!loading && codeStrip.length === 0 ? (
                                    <span className="text-xs text-[#E5E5E5]/50">No projects found for this division.</span>
                                ) : null}
                            </div>
                        </div>
                    </div>
                ) : null}

                <div className="mt-4 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3">
                    <Card title="Total Budget Received" value={formatMoney(kpis.budgetReceived)} sub="Approved budgets" tone="cyan" />
                    <Card title="Total Utilized" value={formatMoney(kpis.utilized)} sub="Purchase utilization" tone="amber" />
                    <Card title="Remaining Budget" value={formatMoney(kpis.remaining)} sub="Budget - utilized" tone="green" />
                    <Card title="Credit Taken" value={formatMoney(kpis.creditTaken)} sub="Loan-eligible cases" tone="red" />
                </div>

                <div className="mt-3 grid grid-cols-1 xl:grid-cols-12 gap-3">
                    <div className="xl:col-span-9 space-y-3">
                        <ChartCard
                            title="Budget vs Utilization"
                            right={
                                <div className="flex items-center gap-2">
                                    <button
                                        type="button"
                                        className={cn(
                                            'text-xs px-2 py-1 rounded-md border',
                                            mode === 'monthly'
                                                ? 'border-cyan-300/60 text-cyan-200 bg-[#0A0F14]'
                                                : 'border-white/10 text-[#E5E5E5]/70 hover:border-cyan-300/30'
                                        )}
                                        onClick={() => setMode('monthly')}
                                    >
                                        Monthly
                                    </button>
                                    <button
                                        type="button"
                                        className={cn(
                                            'text-xs px-2 py-1 rounded-md border',
                                            mode === 'quarterly'
                                                ? 'border-cyan-300/60 text-cyan-200 bg-[#0A0F14]'
                                                : 'border-white/10 text-[#E5E5E5]/70 hover:border-cyan-300/30'
                                        )}
                                        onClick={() => setMode('quarterly')}
                                    >
                                        Quarterly
                                    </button>
                                </div>
                            }
                        >
                            <div className="h-[260px]">
                                <canvas ref={financeCanvasRef} />
                            </div>
                        </ChartCard>

                        <div className="grid grid-cols-1 lg:grid-cols-3 gap-3">
                            <ChartCard title="Project Status">
                                <div className="h-[150px]">
                                    <canvas ref={projectsStatusCanvasRef} />
                                </div>
                            </ChartCard>
                            <ChartCard
                                title="Employees"
                                right={`${kpis.employeesTotal ?? 0} total • ${kpis.employeesHired ?? 0} hired`}
                            >
                                <div className="h-[150px]">
                                    <canvas ref={employeesDonutCanvasRef} />
                                </div>
                            </ChartCard>
                            <ChartCard title="Employees per Project (Top)">
                                <div className="h-[150px]">
                                    <canvas ref={employeesPerProjectCanvasRef} />
                                </div>
                            </ChartCard>
                        </div>

                        <div className={cn('grid grid-cols-1 gap-3', showAllComparisons ? 'lg:grid-cols-2' : 'lg:grid-cols-3')}>
                            <ChartCard
                                title="Purchase Cases per Division"
                                right={`${kpis.reviewedCases ?? 0} reviewed • ${kpis.pendingCases ?? 0} pending`}
                            >
                                <div className="grid grid-rows-2 gap-3">
                                    <div className="h-[120px]">
                                        <canvas ref={purchaseDonutCanvasRef} />
                                    </div>
                                    <div className="h-[120px]">
                                        <canvas ref={purchasePerDivCanvasRef} />
                                    </div>
                                </div>
                            </ChartCard>

                            <ChartCard title="Project Progress (Top)">
                                <div className="h-[240px]">
                                    <canvas ref={projectProgressCanvasRef} />
                                </div>
                            </ChartCard>

                            {!showAllComparisons ? (
                                <ChartCard title="Employees per Division">
                                    <div className="h-[240px]">
                                        <canvas ref={employeesPerDivCanvasRef} />
                                    </div>
                                </ChartCard>
                            ) : null}
                        </div>

                        {showAllComparisons ? (
                            <div className="grid grid-cols-1 lg:grid-cols-3 gap-3">
                                <ChartCard title="All Divisions: Budget vs Utilization">
                                    <div className="h-[240px]">
                                        <canvas ref={budgetVsUtilPerDivCanvasRef} />
                                    </div>
                                </ChartCard>
                                <ChartCard title="All Divisions: Employees">
                                    <div className="h-[240px]">
                                        <canvas ref={employeesPerDivCanvasRef} />
                                    </div>
                                </ChartCard>
                                <ChartCard title="All Divisions: Projects">
                                    <div className="h-[240px]">
                                        <canvas ref={projectsPerDivCanvasRef} />
                                    </div>
                                </ChartCard>
                            </div>
                        ) : null}
                    </div>

                    <div className="xl:col-span-3">
                        <div className="rounded-xl bg-[#121A22]/80 backdrop-blur border border-white/5">
                            <div className="px-4 py-3 flex items-center justify-between gap-3">
                                <div className="text-sm font-semibold">Projects (Codes)</div>
                                <input
                                    value={term}
                                    onChange={(e) => setTerm(e.target.value)}
                                    placeholder="Search…"
                                    className="w-32 bg-[#0A0F14] border border-white/10 rounded-lg px-3 py-2 text-xs text-[#E5E5E5] placeholder:text-[#E5E5E5]/40 focus:outline-none focus:ring-2 focus:ring-cyan-400/30"
                                />
                            </div>

                            <div className="overflow-auto max-h-[760px] px-2 pb-2">
                                {filteredProjects.map((p) => (
                                    <a
                                        key={p.id}
                                        href={`/nrdi/projects/${p.id}`}
                                        className="flex items-center justify-between gap-3 px-3 py-1.5 rounded-lg border border-white/5 hover:bg-[#0A0F14]/60 transition"
                                        title={`${p.code} — ${p.statusRaw ?? ''}`}
                                    >
                                        <div className="min-w-0">
                                            <div className="text-[13px] font-semibold text-cyan-200">{p.code}</div>
                                            <div className="text-[11px] text-[#E5E5E5]/55 truncate">{p.division}</div>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <div className="text-[11px] text-[#E5E5E5]/55 tabular-nums">{p.employees}</div>
                                            <StatusBadge status={p.status} />
                                        </div>
                                    </a>
                                ))}

                                {!loading && filteredProjects.length === 0 ? (
                                    <div className="px-3 py-10 text-center text-[#E5E5E5]/50 text-sm">No projects found.</div>
                                ) : null}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
