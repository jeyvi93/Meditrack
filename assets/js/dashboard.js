/* ANNUAL TOP 5 COMPLAINTS */
new Chart(document.getElementById('complaintsChart'), {
    type: 'bar',
    data: {
        labels: complaintLabels,
        datasets: [{
            label: 'Cases',
            data: complaintCounts,
            backgroundColor: '#3b82f6'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

/* ANNUAL CONSULTATION TREND */
new Chart(document.getElementById('consultationChart'), {
    type: 'line',
    data: {
        labels: [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ],
        datasets: [{
            label: 'Consultations',
            data: consultationMonthlyCounts,
            borderColor: '#f59e0b',
            tension: 0.3,
            fill: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

/* ANNUAL APPOINTMENT TREND */
new Chart(document.getElementById('appointmentChart'), {
    type: 'line',
    data: {
        labels: [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ],
        datasets: [{
            label: 'Appointments',
            data: appointmentMonthlyCounts,
            borderColor: '#0d9488',
            tension: 0.3,
            fill: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

/* DEPARTMENT DISTRIBUTION */

const departmentColors = departmentLabels.map(department => {
    const dept = department.toLowerCase();

    if (dept.includes('technology')) {
        return '#800000'; // Maroon
    }

    if (dept.includes('education')) {
        return '#3b82f6'; // Blue
    }

    if (dept.includes('business')) {
        return '#f59e0b'; // Orange
    }

    return '#64748b'; // Gray fallback
});

new Chart(document.getElementById('departmentChart'), {
    type: 'pie',
    data: {
        labels: departmentLabels,
        datasets: [{
            data: departmentCounts,
            backgroundColor: departmentColors
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

//* APPOINTMENT DEPARTMENT DISTRIBUTION */

const appointmentDepartmentColors = appointmentDeptLabels.map(department => {
    const dept = department.toLowerCase();

    if (dept.includes('technology')) {
        return '#800000'; // Maroon
    }

    if (dept.includes('education')) {
        return '#3b82f6'; // Blue
    }

    if (dept.includes('business')) {
        return '#f59e0b'; // Orange
    }

    return '#64748b'; // Gray fallback
});

new Chart(document.getElementById('appointmentDeptChart'), {
    type: 'pie',
    data: {
        labels: appointmentDeptLabels,
        datasets: [{
            data: appointmentDeptCounts,
            backgroundColor: appointmentDepartmentColors
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

// ==========================
// ACTIVITY MODAL
// ==========================

function openActivityModal() {

    document.getElementById("activityModal").style.display = "flex";

    document.body.style.overflow = "hidden";
}


function closeActivityModal() {

    document.getElementById("activityModal").style.display = "none";

    document.body.style.overflow = "";
}


window.addEventListener("click", function(event) {

    const modal = document.getElementById("activityModal");

    if (event.target === modal) {
        closeActivityModal();
    }

});