        // Toggle Student/Staff Fields
        function togglePatientFields() {
            const type = document.getElementById('patientType').value;
            const studentFields = document.getElementById('studentFields');
            const staffFields = document.getElementById('staffFields');

            if (type === 'student') {
                studentFields.style.display = 'grid';
                staffFields.style.display = 'none';
            } else {
                studentFields.style.display = 'none';
                staffFields.style.display = 'block';
            }
        }

        // Add Dynamic Medicine Rows
        function addMedicineRow() {
            const container = document.getElementById('medicineRows');
            const newRow = document.createElement('div');
            newRow.className = 'medicine-row';
            newRow.innerHTML = `
                <div class="input-group">
                    <select class="med-select">
                        <option value="">Select Medicine from Inventory</option>
                        <option>Paracetamol 500mg</option>
                        <option>Amoxicillin 250mg</option>
                        <option>Cetirizine 10mg</option>
                    </select>
                </div>
                <div class="input-group qty-input">
                    <input type="number" min="1" value="1">
                </div>
                <div class="row-action">
                    <button type="button" class="btn-remove" onclick="this.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            container.appendChild(newRow);
        }