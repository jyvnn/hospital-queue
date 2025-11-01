// Progressive enhancement for queue actions: intercept assign/complete forms and POST via fetch
(function(){
    const waitingContainer = document.getElementById('waitingQueue');
    const inProgressContainer = document.getElementById('inProgressQueue');

    if (!waitingContainer && !inProgressContainer) return;

    // helper to find CSRF token from meta or hidden input
    function getCsrfToken() {
        const m = document.querySelector('meta[name="csrf-token"]');
        if (m) return m.getAttribute('content');
        const input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    const csrf = getCsrfToken();

    function updateCounts(){
        const waitingCount = document.getElementById('waitingCount');
        const inProgressCount = document.getElementById('inProgressCount');
        if (waitingCount) waitingCount.textContent = (waitingContainer ? waitingContainer.querySelectorAll('.card.mb-4').length : 0) + ' patients waiting';
        if (inProgressCount) inProgressCount.textContent = (inProgressContainer ? inProgressContainer.querySelectorAll('.card.mb-4').length : 0) + ' ongoing consultations';
    }

    // helper to create an in-progress card from patient + doctor
    function makeInProgressCard(patient, doctorName){
        const wrapper = document.createElement('div');
        wrapper.className = 'card mb-4';
        wrapper.innerHTML = `
            <div class="card-body d-flex justify-content-between align-items-start">
                <div style="flex:1">
                    <h5 class="mb-1">${patient.name} <small class="text-muted">(${patient.age || ''})</small></h5>
                    <div class="small text-muted">With Dr. ${doctorName || '—'}</div>
                    <div class="small text-muted">Service: ${patient.service_type || '—'}</div>
                </div>
                <div style="min-width:220px; margin-left:20px; text-align:right;">
                    <form action="/patients/complete" method="POST" class="js-complete-form">
                        <input type="hidden" name="_token" value="${csrf}">
                        <input type="hidden" name="patient_id" value="${patient.id}">
                        <button type="submit" class="btn btn-primary">Complete</button>
                    </form>
                </div>
            </div>
        `;
        return wrapper;
    }

    // event delegation for submit on assign forms
    document.addEventListener('submit', function(e){
        const form = e.target;
        if (!form) return;
        // Assign doctor forms
        if (form.action && form.action.endsWith('/patients/assign-doctor')){
            e.preventDefault();
            const fd = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                body: fd
            }).then(r => r.json()).then(json => {
                if (!json || !json.success) return;
                // remove waiting card
                const card = form.closest('.card.mb-4');
                if (card && waitingContainer) card.remove();
                // add to in-progress
                if (inProgressContainer && json.patient){
                    const cardEl = makeInProgressCard(json.patient, json.doctor);
                    inProgressContainer.insertBefore(cardEl, inProgressContainer.firstChild);
                }
                updateCounts();
            }).catch(err => {
                console.error('Assign via AJAX failed', err);
                // fallback: submit the form normally
                form.submit();
            });
        }

        // Complete consultation forms
        if (form.action && form.action.endsWith('/patients/complete')){
            e.preventDefault();
            const fd = new FormData(form);
            fetch(form.action, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
                body: fd
            }).then(r => r.json()).then(json => {
                if (!json || !json.success) return;
                // remove in-progress card
                const card = form.closest('.card.mb-4');
                if (card && inProgressContainer) card.remove();
                updateCounts();
            }).catch(err => {
                console.error('Complete via AJAX failed', err);
                form.submit();
            });
        }
    });

})();
