document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("accountSearch");
    const roleFilter = document.getElementById("roleFilter");

    if (!searchInput || !roleFilter) {
        return;
    }

    let searchTimeout;
    let requestController;
    let requestId = 0;

    let currentPage = Math.max(
        1,
        parseInt(new URLSearchParams(window.location.search).get("page") || "1", 10)
    );

    async function refreshAccountList(resetPage = false, focusSearch = false) {

        if (resetPage) {
            currentPage = 1;
        }

        // Cancel an older request so stale results cannot overwrite newer ones.
        if (requestController) {
            requestController.abort();
        }

        requestController = new AbortController();

        const thisRequestId = ++requestId;

        const params = new URLSearchParams();
        const search = searchInput.value.trim();
        const role = roleFilter.value;

        if (search !== "") {
            params.set("search", search);
        }

        if (role !== "") {
            params.set("role", role);
        }

        params.set("page", String(currentPage));

        try {

            const response = await fetch(
                "accounts.php?" + params.toString(),
                {
                    signal: requestController.signal,
                    cache: "no-store"
                }
            );

            if (!response.ok) {
                throw new Error("Failed to refresh accounts.");
            }

            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");

            // Ignore results from an outdated request.
            if (thisRequestId !== requestId) {
                return;
            }

            const newTable = doc.querySelector(".table-container table");
            const currentTable = document.querySelector(".table-container table");

            const newFooter = doc.querySelector(".table-footer");
            const currentFooter = document.querySelector(".table-footer");

            if (newTable && currentTable && newFooter && currentFooter) {

                currentTable.innerHTML = newTable.innerHTML;
                currentFooter.innerHTML = newFooter.innerHTML;

                // Keep the page number in sync if pagination changes.
                const pageText = newFooter.querySelector(".pagination span");
                const pageMatch = pageText?.textContent.match(/Page\s+(\d+)\s+of/i);

                currentPage = pageMatch ? parseInt(pageMatch[1], 10) : 1;
            }

            if (focusSearch) {
                searchInput.focus();

                searchInput.setSelectionRange(
                    searchInput.value.length,
                    searchInput.value.length
                );
            }

        } catch (error) {

            if (error.name !== "AbortError") {
                console.error("Account list refresh error:", error);
            }

        }
    }

    function applyFilters() {

        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(function () {
            refreshAccountList(true, true);
        }, 300);
    }

    searchInput.addEventListener("input", applyFilters);

    roleFilter.addEventListener("change", applyFilters);

    // Refresh accounts, count, and pagination every 10 seconds.
    setInterval(function () {

        if (!document.hidden) {
            refreshAccountList(false, false);
        }

    }, 10000);

    // =========================
    // ACCOUNT ACTION DROPDOWN
    // =========================

    document.addEventListener("click", function (e) {

        const menuButton = e.target.closest(".account-menu-btn");

        if (menuButton) {
            e.stopPropagation();

            const dropdown = menuButton.nextElementSibling;
            const isOpen = dropdown.classList.contains("active");

            document.querySelectorAll(".account-dropdown.active").forEach(menu => {
                menu.classList.remove("active");
            });

            if (!isOpen) {
                dropdown.classList.add("active");
            }

            return;
        }

        if (!e.target.closest(".account-dropdown")) {
            document.querySelectorAll(".account-dropdown.active").forEach(menu => {
                menu.classList.remove("active");
            });
        }

    });

        // =========================
        // DELETE ACCOUNT MODAL
        // =========================

        const deleteAccountModal = document.getElementById("deleteAccountModal");
        let selectedAccountId = null;

        const deleteSuccessModal = document.getElementById("deleteSuccessModal");
        const closeDeleteSuccessModal = document.getElementById("closeDeleteSuccessModal");

        if (closeDeleteSuccessModal && deleteSuccessModal) {
            closeDeleteSuccessModal.addEventListener("click", function () {
                deleteSuccessModal.classList.remove("active");
            });
        }

        document.addEventListener("click", function (e) {

            const deleteButton = e.target.closest(".delete-account-btn");

            if (deleteButton) {
                selectedAccountId = deleteButton.dataset.id;

                document.querySelectorAll(".account-dropdown.active").forEach(menu => {
                    menu.classList.remove("active");
                });

                deleteAccountModal.classList.add("active");
                return;
            }

            if (e.target.closest("#cancelDeleteAccount")) {
                deleteAccountModal.classList.remove("active");
                selectedAccountId = null;
            }

        });

            if (deleteAccountModal) {
            deleteAccountModal.addEventListener("click", function (e) {
                if (e.target === deleteAccountModal) {
                    deleteAccountModal.classList.remove("active");
                    selectedAccountId = null;
                }
            });
        }

        // CONFIRM ACCOUNT DELETION
        const confirmDeleteButton = document.getElementById("confirmDeleteAccount");

        if (confirmDeleteButton) {
            confirmDeleteButton.addEventListener("click", async function () {

            if (!selectedAccountId) return;

            const csrfToken = document.getElementById("deleteAccountCsrfToken").value;

            confirmDeleteButton.disabled = true;
            confirmDeleteButton.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Deleting...';

            try {

                const response = await fetch("delete_unverified_account.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: new URLSearchParams({
                        account_id: selectedAccountId,
                        csrf_token: csrfToken
                    })
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.message || "Unable to delete account.");
                }

                deleteAccountModal.classList.remove("active");
                selectedAccountId = null;

                await refreshAccountList();

                deleteSuccessModal.classList.add("active");

            } catch (error) {

                alert(error.message);

            } finally {

                confirmDeleteButton.disabled = false;
                confirmDeleteButton.innerHTML = '<i class="bx bx-trash"></i> Delete Account';

                        }

            });
        }

});


    // =========================
    // ADMIN SIDEBAR LOGOUT MENU
    // =========================

    function toggleLogoutMenu() {
        const menu = document.getElementById("logoutMenu");

        if (!menu) return;

        const isVisible = window.getComputedStyle(menu).display !== "none";

        menu.style.display = isVisible ? "none" : "block";
    }

    document.addEventListener("click", function (event) {
        const menu = document.getElementById("logoutMenu");
        const adminCard = document.querySelector(".admin-card");

        if (
            menu &&
            adminCard &&
            !menu.contains(event.target) &&
            !adminCard.contains(event.target)
        ) {
            menu.style.display = "none";
        }
});


// =========================
// LOGOUT CONFIRMATION MODAL
// =========================

function openLogoutModal() {
    const modal = document.getElementById("logoutModal");

    if (modal) {
        modal.classList.add("active");
    }
}

function closeLogoutModal() {
    const modal = document.getElementById("logoutModal");

    if (modal) {
        modal.classList.remove("active");
    }
}

function confirmLogout() {
    const confirmationModal = document.getElementById("logoutModal");
    const processingModal = document.getElementById("logoutProcessingModal");
    const logoutButton = document.querySelector(".logout-confirm");

    if (confirmationModal) {
        confirmationModal.classList.remove("active");
    }

    if (processingModal) {
        processingModal.classList.add("active");
    }

    if (logoutButton) {
        logoutButton.disabled = true;
        logoutButton.innerHTML = '<i class="bx bx-log-out"></i> Signing Out...';
    }

    window.location.href = "../auth/logout.php";
}

document.addEventListener("click", function (event) {
    const modal = document.getElementById("logoutModal");

    if (event.target === modal) {
        closeLogoutModal();
    }
});

document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
        closeLogoutModal();
    }
});

function closePersonnelSuccessModal() {
    const modal = document.getElementById('personnelSuccessModal');

    if (modal) {
        modal.remove();
    }
}

function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);

    if (!input) return;

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("bx-show");
        icon.classList.add("bx-hide");
    } else {
        input.type = "password";
        icon.classList.remove("bx-hide");
        icon.classList.add("bx-show");
    }
}

