// ==========================
// DAILY VOLUME CHART
// ==========================

const dailyCanvas = document.getElementById('dailyVolumeChart');

if (dailyCanvas) {

    const dailyCtx = dailyCanvas.getContext('2d');

    new Chart(dailyCtx, {
        type: 'line',

        data: {
            labels: window.reportData.dailyLabels,

            datasets: [{
                label: 'Daily Consultations',

                data: window.reportData.dailyData,

                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',

                fill: true,
                tension: 0.4
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            plugins: {
                legend: {
                    display: false
                }
            },

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
}


// ==========================
// PATIENT TYPE CHART
// ==========================

const typeCanvas = document.getElementById('patientTypeChart');

if (typeCanvas) {

    const typeCtx = typeCanvas.getContext('2d');

    new Chart(typeCtx, {
        type: 'doughnut',

        data: {
            labels: [
                'Student',
                'Teaching Staff',
                'Non-Teaching Staff'
            ],

            datasets: [{
                data: [
                    window.reportData.studentVolume,
                    window.reportData.teachingStaffVolume,
                    window.reportData.nonTeachingStaffVolume
                ],

                backgroundColor: [
                    '#1e40af',
                    '#0d9488',
                    '#3b82f6'
                ],

                borderWidth: 0
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            cutout: '70%',

            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}


// ==========================
// CHANGE REPORT
// ==========================

function changeReport() {

    const month =
        document.getElementById("monthSelect").value;

    const year =
        document.getElementById("yearSelect").value;

    window.location.href =
        `reports.php?month=${month}&year=${year}`;
}