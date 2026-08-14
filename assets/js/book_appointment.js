function openAppointmentModal(event) {
    event.preventDefault();

    document.getElementById('confirmAppointmentModal')
    .classList.remove('hidden');
}

function closeAppointmentModal() {
    document.getElementById('confirmAppointmentModal')
    .classList.add('hidden');
}

function confirmAppointment() {
    // Close confirmation modal
    closeAppointmentModal();

    // Show success modal
    document.getElementById('successAppointmentModal')
        .classList.remove('hidden');
}

function closeSuccessModal() {
    document.getElementById('successAppointmentModal')
        .classList.add('hidden');
}

function openLogoutModal() {
    document.getElementById('logoutModal')
        .classList.remove('hidden');
}

function closeLogoutModal() {
    document.getElementById('logoutModal')
        .classList.add('hidden');
}

function confirmLogout() {
    window.location.href = "../index.php";
}