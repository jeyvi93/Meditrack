// ===============================
// REFERRAL MODAL
// ===============================

function openModal(id) {

    fetch(`../actions/get_referral.php?id=${id}`)
        .then(response => response.json())
        .then(data => {

            if (!data.success) {
                alert(data.message || "Failed to load referral.");
                return;
            }

            const referral = data.referral;

            document.getElementById("modalReferredDate").textContent =
                referral.referred_date || "—";

            document.getElementById("modalDoctor").textContent =
                referral.doctor_name || "—";

            document.getElementById("modalReason").textContent =
                referral.reason_for_referral || "—";

            document.getElementById("modalFullName").textContent =
                referral.full_name || "—";

            document.getElementById("modalSex").textContent =
                referral.sex || "—";

            document.getElementById("modalDob").textContent =
                referral.date_of_birth || "—";

            document.getElementById("modalContact").textContent =
                referral.contact_number || "—";

            document.getElementById("modalAddress").textContent =
                referral.address || "—";

            document.getElementById("modalParentName").textContent =
                referral.parent_name || "—";

            document.getElementById("modalParentDob").textContent =
                referral.parent_dob || "—";

            document.getElementById("modalSpouseName").textContent =
                referral.spouse_name || "—";

            document.getElementById("modalSpouseDob").textContent =
                referral.spouse_dob || "—";

            document.getElementById("modalPhilhealthMember").textContent =
                Number(referral.is_philhealth_member) === 1 ? "Yes" : "No";

            document.getElementById("modalPhilhealthNumber").textContent =
                referral.philhealth_number || "—";

            document.getElementById("modalBP").textContent =
                referral.blood_pressure || "—";

            document.getElementById("modalWeight").textContent =
                referral.weight ? referral.weight + " kg" : "—";

            document.getElementById("modalTemperature").textContent =
                referral.temperature ? referral.temperature + " °C" : "—";

            document.getElementById("modalNurseNotes").textContent =
                referral.nurse_notes || "—";

            document.getElementById("referralModal").style.display = "flex";

        })
        .catch(error => {

            console.error(error);
            alert("Something went wrong while loading the referral.");

        });
}

function closeModal() {
    document.getElementById("referralModal").style.display = "none";
}

const modal = document.getElementById("referralModal");

if (modal) {
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });
}

document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
        closeModal();
    }
});

// ===============================
// AJAX LIVE SERVER-SIDE SEARCH
// ===============================

const searchInput = document.getElementById("searchInput");

if (searchInput) {

    let searchTimeout;
    let searchController;

    searchInput.addEventListener("input", function () {

        clearTimeout(searchTimeout);

        searchTimeout = setTimeout(async () => {

            const search = searchInput.value.trim();

            if (searchController) {
                searchController.abort();
            }

            searchController = new AbortController();

            const params = new URLSearchParams();

            if (search !== "") {
                params.set("search", search);
            }

            // ALWAYS RESET TO PAGE 1
            params.set("page", "1");

            try {

                const response = await fetch(
                    "referral_history.php?" + params.toString(),
                    {
                        signal: searchController.signal
                    }
                );

                const html = await response.text();

                const parser = new DOMParser();

                const doc = parser.parseFromString(
                    html,
                    "text/html"
                );


                // ===============================
                // UPDATE TABLE
                // ===============================

                const newTable =
                    doc.querySelector(".logs-table");

                const currentTable =
                    document.querySelector(".logs-table");

                if (newTable && currentTable) {

                    currentTable.innerHTML =
                        newTable.innerHTML;

                }


                // ===============================
                // UPDATE FOOTER
                // ===============================

                const newFooter =
                    doc.querySelector(".table-footer");

                const currentFooter =
                    document.querySelector(".table-footer");

                if (newFooter && currentFooter) {

                    currentFooter.innerHTML =
                        newFooter.innerHTML;

                }


                // ===============================
                // KEEP SEARCH FOCUSED
                // ===============================

                searchInput.focus();

                searchInput.setSelectionRange(
                    searchInput.value.length,
                    searchInput.value.length
                );


            } catch (error) {

                if (error.name !== "AbortError") {

                    console.error(
                        "Referral search error:",
                        error
                    );

                }

            }

        }, 300);

    });

}