import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

// Reports chart module: finds the canvas and controls in the Reports tab and renders the Patients-over-time chart
(function(){
    const canvas = document.getElementById('patientsOverTimeChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let chart = null;

    function isoDate(d){
        return d.toISOString().slice(0,10);
    }

    function setRange(days){
        const to = new Date();
        const from = new Date();
        from.setDate(to.getDate() - (days-1));
        const fromEl = document.getElementById('from');
        const toEl = document.getElementById('to');
        if (fromEl) fromEl.value = isoDate(from);
        if (toEl) toEl.value = isoDate(to);
    }

    function fetchAndRender(){
        const fromEl = document.getElementById('from');
        const toEl = document.getElementById('to');
        const from = fromEl ? fromEl.value : '';
        const to = toEl ? toEl.value : '';
        const url = `/reports/patients-per-day?from=${from}&to=${to}&granularity=day`;
        fetch(url)
            .then(res => res.json())
            .then(json => {
                if (!json || !json.success) return;
                const labels = json.data.labels || [];
                const counts = json.data.counts || [];

                const data = {
                    labels: labels,
                    datasets: [{
                        label: 'Patients',
                        data: counts,
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37,99,235,0.08)',
                        fill: true,
                        tension: 0.2,
                        pointRadius: 3,
                    }]
                };

                const cfg = {
                    type: 'line',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { display: true, title: { display: false } },
                            y: { display: true, beginAtZero: true }
                        },
                        plugins: { tooltip: { mode: 'index', intersect: false } }
                    }
                };

                if (chart) chart.destroy();
                chart = new Chart(ctx, cfg);
            })
            .catch(err => {
                // keep failure silent in production UIs, but log to console during development
                console.error('Failed to load patients per day', err);
            });
    }

    // Wire up controls if present
    const preset7 = document.getElementById('preset7');
    const preset30 = document.getElementById('preset30');
    const preset90 = document.getElementById('preset90');
    const applyRange = document.getElementById('applyRange');

    if (preset7) preset7.addEventListener('click', function(){ setRange(7); fetchAndRender(); });
    if (preset30) preset30.addEventListener('click', function(){ setRange(30); fetchAndRender(); });
    if (preset90) preset90.addEventListener('click', function(){ setRange(90); fetchAndRender(); });
    if (applyRange) applyRange.addEventListener('click', fetchAndRender);

    // initialize with 30 days
    setRange(30);
    fetchAndRender();

})();
