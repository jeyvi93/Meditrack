// ==========================
    // LOGOUT MODAL
    // ==========================

    function openLogoutModal() {
        document.getElementById('logoutModal').classList.remove('hidden');
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').classList.add('hidden');
    }

    function confirmLogout() {
        window.location.href = '../index.html';
    }


    // ==========================
    // PASSWORD SUCCESS MODAL
    // ==========================

    const passwordForm = document.querySelector('.password-form');

    passwordForm.addEventListener('submit', function(event) {
        event.preventDefault();

        document.getElementById('passwordSuccessModal').classList.remove('hidden');
    });

    function closePasswordSuccessModal() {
        document.getElementById('passwordSuccessModal').classList.add('hidden');
    }
