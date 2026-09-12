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
    closeAppointmentModal();

    document.querySelector('.appointment-card').submit();
}

function closeSuccessModal() {
    document.getElementById('successAppointmentModal')
        .classList.add('hidden');
}

function closeErrorModal() {
    document.getElementById("errorAppointmentModal").classList.add("hidden");
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
    window.location.href = "../auth/logout.php";
}