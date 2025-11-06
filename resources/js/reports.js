import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

// Reports module: initializes Patients-over-time (line), Priority (bar), Service Type (pie)
const Reports = (function () {
    function isoDate(d) { return d.toISOString().slice(0, 10); }

    // Patients over time
    let timeChart = null;
    function fetchAndRenderTime() {
        const canvas = document.getElementById('patientsOverTimeChart');
        if (!canvas) return Promise.resolve();
        const ctx = canvas.getContext('2d');
        const fromEl = document.getElementById('from');
        const toEl = document.getElementById('to');
        const from = fromEl ? fromEl.value : '';
        const to = toEl ? toEl.value : '';
        const url = `/reports/patients-per-day?from=${from}&to=${to}&granularity=day`;
        return fetch(url)
            .then(r => r.json())
            .then(j => {
                if (!j || !j.success) return;
                const labels = j.data.labels || [];
                const counts = j.data.counts || [];
                const cfg = {
                    type: 'line',
                    data: { labels, datasets: [{ label: 'Patients', data: counts, borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.08)', fill: true, tension: 0.2, pointRadius: 3 }] },
                    options: { responsive: true, maintainAspectRatio: false, scales: { x: { display: true }, y: { beginAtZero: true } }, plugins: { tooltip: { mode: 'index', intersect: false } } }
                };
                if (timeChart) timeChart.destroy();
                timeChart = new Chart(ctx, cfg);
            })
            .catch(err => console.error('Failed to load patients per day', err));
    }

    function setRange(days) {
        const to = new Date();
        const from = new Date();
        from.setDate(to.getDate() - (days - 1));
        const fromEl = document.getElementById('from');
        const toEl = document.getElementById('to');
        if (fromEl) fromEl.value = isoDate(from);
        if (toEl) toEl.value = isoDate(to);
    }

    function wireTimeControls() {
        const preset7 = document.getElementById('preset7');
        const preset30 = document.getElementById('preset30');
        const preset90 = document.getElementById('preset90');
        const applyRange = document.getElementById('applyRange');
        if (preset7) preset7.addEventListener('click', () => { setRange(7); fetchAndRenderTime(); });
        if (preset30) preset30.addEventListener('click', () => { setRange(30); fetchAndRenderTime(); });
        if (preset90) preset90.addEventListener('click', () => { setRange(90); fetchAndRenderTime(); });
        if (applyRange) applyRange.addEventListener('click', fetchAndRenderTime);
    }

    // Priority (bar/column)
    let priorityChart = null;
    function fetchPriority() {
        const canvas = document.getElementById('priorityChart');
        if (!canvas) return Promise.resolve();
        const ctx = canvas.getContext('2d');
        return fetch('/reports/by-priority')
            .then(r => r.json())
            .then(j => {
                if (!j || !j.success) return;
                const labels = j.data.labels || [];
                const counts = j.data.counts || [];
                const cfg = { type: 'bar', data: { labels, datasets: [{ label: 'Patients', data: counts, backgroundColor: ['#ef4444', '#10b981'] }] }, options: { responsive: true, maintainAspectRatio: false, scales: { x: { ticks: { autoSkip: false } }, y: { beginAtZero: true } }, plugins: { legend: { display: false } } } };
                if (priorityChart) priorityChart.destroy();
                priorityChart = new Chart(ctx, cfg);
            })
            .catch(e => console.error('Failed to load priority data', e));
    }

    // Service type (pie)
    let serviceChart = null;
    function fetchServiceTypes() {
        const canvas = document.getElementById('serviceTypeChart');
        if (!canvas) return Promise.resolve();
        const ctx = canvas.getContext('2d');
        return fetch('/reports/by-service-type')
            .then(r => r.json())
            .then(j => {
                if (!j || !j.success) return;
                const labels = j.data.labels || [];
                const counts = j.data.counts || [];
                const palette = ['#3b82f6', '#06b6d4', '#8b5cf6', '#f97316', '#ef4444', '#10b981', '#f59e0b'];
                const colors = labels.map((_, i) => palette[i % palette.length]);
                const cfg = { type: 'pie', data: { labels, datasets: [{ data: counts, backgroundColor: colors }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } };
                if (serviceChart) serviceChart.destroy();
                serviceChart = new Chart(ctx, cfg);
            })
            .catch(e => console.error('Failed to load service-type data', e));
    }

    function init() {
        setRange(30);
        wireTimeControls();
        return Promise.all([fetchAndRenderTime(), fetchPriority(), fetchServiceTypes()]);
    }

    function refresh() {
        return Promise.all([fetchAndRenderTime(), fetchPriority(), fetchServiceTypes()]);
    }

    return { init, refresh };
})();

window.addEventListener('DOMContentLoaded', () => {
    try { Reports.init(); } catch (e) { console.error('Reports init failed', e); }
    // attach to application namespace instead of polluting global scope
    window.App = window.App || {};
    window.App.Reports = Reports;
});
