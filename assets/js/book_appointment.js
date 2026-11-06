// REMOVE EMOJIS FROM PURPOSE OF VISIT
const purposeInput = document.querySelector('textarea[name="purpose"]');

if (purposeInput) {
    purposeInput.addEventListener("input", function () {
        this.value = this.value.replace(
            /[\p{Extended_Pictographic}\p{Emoji_Presentation}\p{Emoji_Modifier}\p{Regional_Indicator}\uFE0F\u200D\u20E3]/gu,
            ""
        );
    });
}

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
    const confirmationModal = document.getElementById('confirmAppointmentModal');
    const processingModal = document.getElementById('processingAppointmentModal');
    const bookingForm = document.querySelector('.appointment-card');

    if (!bookingForm || !processingModal) return;

    // Close confirmation modal
    confirmationModal.classList.add('hidden');

    // Show processing modal
    processingModal.classList.remove('hidden');

    // Prevent duplicate submissions
    const confirmButton = confirmationModal.querySelector('.btn-yes');
    const submitButton = bookingForm.querySelector('.btn-submit-booking');

    if (confirmButton) {
        confirmButton.disabled = true;
    }

    if (submitButton) {
        submitButton.disabled = true;
    }

    // Allow the processing modal to render before submitting
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            bookingForm.submit();
        });
    });
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