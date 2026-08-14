
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
        window.location.href = '../index.php';
    }


    // ==========================
    // PROFILE UPDATE SUCCESS
    // ==========================

    function handleProfileSubmit(event) {
        event.preventDefault();

        document.getElementById('successModal').classList.remove('hidden');
    }

    function closeSuccessModal() {
        document.getElementById('successModal').classList.add('hidden');
    }
