// ==========================================
// DISABLE PAST APPOINTMENT TIME SLOTS
// ==========================================

const appointmentDate = document.getElementById('appointmentDate');
const appointmentTime = document.getElementById('appointmentTime');

function updateAvailableTimeSlots() {
    if (!appointmentDate || !appointmentTime) return;

    const selectedDate = appointmentDate.value;

    // Get current Philippine date and time
    const manilaNow = new Date().toLocaleString('en-US', {
        timeZone: 'Asia/Manila'
    });

    const currentDateTime = new Date(manilaNow);

    const today = [
        currentDateTime.getFullYear(),
        String(currentDateTime.getMonth() + 1).padStart(2, '0'),
        String(currentDateTime.getDate()).padStart(2, '0')
    ].join('-');

    const currentTime = [
        String(currentDateTime.getHours()).padStart(2, '0'),
        String(currentDateTime.getMinutes()).padStart(2, '0'),
        '00'
    ].join(':');

    Array.from(appointmentTime.options).forEach(option => {
        if (!option.value) return;

        const isPast =
            selectedDate === today &&
            option.value <= currentTime;

        option.disabled = isPast;

        if (isPast && option.selected) {
            appointmentTime.value = '';
        }
    });
}

// Update when the page loads
updateAvailableTimeSlots();

// Update whenever the appointment date changes
if (appointmentDate) {
    appointmentDate.addEventListener('change', updateAvailableTimeSlots);
}

// Recheck when the user returns to the booking page
window.addEventListener('focus', updateAvailableTimeSlots);

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