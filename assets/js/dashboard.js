/* TOP 5 COMPLAINTS */
new Chart(document.getElementById('complaintsChart'), {
    type: 'bar',
    data: {
        labels: ['Headache', 'Fever', 'Cough', 'Stomach Pain', 'Dizziness'],
        datasets: [{
            label: 'Cases',
            data: [30, 25, 18, 12, 10],
            backgroundColor: '#3b82f6'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y'
    }
});

/* CONSULTATION TREND */
new Chart(document.getElementById('consultationChart'), {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Consultations',
            data: [15, 22, 18, 30, 28, 40],
            borderColor: '#f59e0b',
            tension: 0.3
        }]
    }
});

/* APPOINTMENT TREND */
new Chart(document.getElementById('appointmentChart'), {
    type: 'line',
    data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'Appointments',
            data: [20, 30, 25, 40, 35, 50],
            borderColor: '#0d9488',
            tension: 0.3
        }]
    }
});

/* PIE CHART - DEPARTMENTS */
new Chart(document.getElementById('departmentChart'), {
    type: 'pie',
    data: {
        labels: ['Education', 'Technology', 'Business'],
        datasets: [{
            data: [45, 30, 25],
            backgroundColor: [
                '#3b82f6',
                '#800000',
                '#f59e0b'
            ]
        }]
    }
});
/* Appointment PIE CHART - DEPARTMENTS */
new Chart(document.getElementById('appointmentDeptChart'), {
    type: 'pie',
    data: {
        labels: ['Education', 'Technology', 'Business'],
        datasets: [{
            data: [20, 30, 25],
            backgroundColor: [
                '#3b82f6',
                '#800000',
                '#f59e0b'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// OPEN MODAL
function openCancelModal(name) {
    document.getElementById("cancelModal").style.display = "flex";
    document.getElementById("canPatientName").value = name;
}

// CLOSE MODAL
function closeCancelModal() {
    document.getElementById("cancelModal").style.display = "none";
}

// CLICK OUTSIDE TO CLOSE
window.addEventListener("click", function (e) {
    const modal = document.getElementById("cancelModal");
    if (e.target === modal) {
        modal.style.display = "none";
    }
});

// FORM SUBMIT
document.getElementById("cancelForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const reason = document.getElementById("canReason").value;
    const notes = document.getElementById("rejNotes").value;

    if (!reason) return;

    console.log("CANCEL DATA:");
    console.log("Reason:", reason);
    console.log("Notes:", notes);

    // TODO: later PHP AJAX call here

    alert("Appointment cancelled successfully!");

    closeCancelModal();
});