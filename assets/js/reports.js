// Daily Volume Chart (Line)
        const dailyCtx = document.getElementById('dailyVolumeChart').getContext('2d');
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: ['1', '4', '8', '12', '16', '20', '24', '28', '31'],
                datasets: [{
                    label: 'Daily Consultations',
                    data: [12, 19, 15, 24, 10, 18, 14, 22, 11],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // Patient Type Chart (Doughnut)
        const typeCtx = document.getElementById('patientTypeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: ['Student', 'Staff'],
                datasets: [{
                    data: [250, 92],
                    backgroundColor: ['#1e40af', '#0d9488'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: { legend: { position: 'bottom' } }
            }
        });

