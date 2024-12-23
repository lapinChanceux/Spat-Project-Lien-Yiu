document.addEventListener('DOMContentLoaded', () => {
    const navLinks = document.querySelectorAll('.nav-link');

    // Set the "Home" link as active by default
    navLinks.forEach(nav => nav.classList.remove('active')); // Ensure all are inactive first
    document.querySelector('a[href="index.php?page=home#home-section"]').classList.add('active');

    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            // Remove 'active' class from all nav links
            navLinks.forEach(nav => nav.classList.remove('active'));

            // Add 'active' class to the clicked nav link
            link.classList.add('active');
        });
    });
});


function redirectToSection() {
    window.location.href = 'index.php';
}



//Update Appointment Status
document.addEventListener('DOMContentLoaded', () => {
    const updateStatusModal = document.getElementById('updateStatusModal');
    const appointmentIdInput = document.getElementById('appointmentId');
    const statusSelect = document.getElementById('status');

    updateStatusModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const appointmentId = button.getAttribute('data-appointment-id');
        const currentStatus = button.getAttribute('data-status');

        console.log('Modal is triggered', appointmentId, currentStatus); // Log this to check if it triggers
        appointmentIdInput.value = appointmentId;
        if (currentStatus) {
            for (let option of statusSelect.options) {
                if (option.value === currentStatus) {
                    option.selected = true;
                } else {
                    option.selected = false;
                }
            }
        }
    });
});

// Validation Script
(function () {
    'use strict';

    const form = document.getElementById('appointmentForm');
    const fullNameInput = document.getElementById('fullName');
    const carNumberInput = document.getElementById('carNumber');
    const appointmentDateInput = document.getElementById('appointmentDate');
    const appointmentTimeInput = document.getElementById('appointmentTime');
    const carBrandInput = document.getElementById('carBrand');
    const carModelInput = document.getElementById('carModel');

    function validateName() {
        if (/\d/.test(fullNameInput.value.trim())) {
            fullNameInput.setCustomValidity('Name cannot contain numbers.');
        } else {
            fullNameInput.setCustomValidity('');
        }
    }

    function validateCarNumber() {
        const carNumberValue = carNumberInput.value.trim();
        if (carNumberValue === '') {
            carNumberInput.setCustomValidity('Car number is required.');
        } else if (/\s/.test(carNumberValue)) {
            carNumberInput.setCustomValidity('Car number cannot contain spaces.');
        } else {
            carNumberInput.setCustomValidity('');
        }
    }

    function validateDate() {
        const selectedDate = new Date(appointmentDateInput.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (selectedDate <= today) {
            appointmentDateInput.setCustomValidity('Date cannot be today or a past date.');
        } else {
            appointmentDateInput.setCustomValidity('');
        }
    }

    function validateTime() {
        const selectedTime = appointmentTimeInput.value;
        const [hours, minutes] = selectedTime.split(':').map(Number);

        if (hours < 9 || hours > 17 || (hours === 17 && minutes > 0)) {
            appointmentTimeInput.setCustomValidity('Time must be between 9:00 AM and 5:00 PM.');
        } else {
            appointmentTimeInput.setCustomValidity('');
        }
    }

    function validateCarBrand() {
        if (carBrandInput.value === '') {
            carBrandInput.setCustomValidity('Please select a valid car brand.');
        } else {
            carBrandInput.setCustomValidity('');
        }
    }

    function validateCarModel() {
        const selectedValue = carModelInput.value;
        if (selectedValue === '' || selectedValue === 'Select Car Model') {
            carModelInput.setCustomValidity('Please select a valid car model.');
        } else {
            carModelInput.setCustomValidity('');
        }
    }

    // Attach real-time validation listeners
    fullNameInput.addEventListener('input', validateName);
    carNumberInput.addEventListener('input', validateCarNumber);
    appointmentDateInput.addEventListener('input', validateDate);
    appointmentTimeInput.addEventListener('input', validateTime);
    carBrandInput.addEventListener('change', function () {
        validateCarBrand();
        validateCarModel();
    });

    carModelInput.addEventListener('change', validateCarModel);

    // Validate on form submission
    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        // Add Bootstrap validation class
        form.classList.add('was-validated');
    });
})();




//Delete script
function setappointment_id(event) {
    let button = event.currentTarget; // Button that triggered the modal
    let appointmentId = button.getAttribute('data-appointment-id'); // Get the appointment ID from the button
    let deleteAppointmentId = document.getElementById('deleteAppointmentId'); // Get the hidden input field
    deleteAppointmentId.value = appointmentId; // Set the value of the hidden input to the appointment ID
}

// Submit the delete form
function submitDeleteForm() {
    document.getElementById('deleteForm').submit();
}

// Show the appointment detail
function populateDetailModal(event) {
    const button = event.currentTarget;

    // Extract data attributes
    const name = button.getAttribute('data-name');
    const carNumber = button.getAttribute('data-car-number');
    const date = button.getAttribute('data-date');

    // Populate modal content
    const modalBody = document.querySelector('#detailModal .modal-body .text-center');
    modalBody.innerHTML = `<p>Hi Admin, <strong>${name}</strong> has an appointment with car place plate 
            <strong>${carNumber}</strong> on <strong>${date}</strong>.</p>`;
}


document.addEventListener("DOMContentLoaded", function () {
    const appointmentTable = document.getElementById("appointmentTable");
    const rows = appointmentTable.querySelectorAll("tbody tr");
    const navLinks = document.querySelectorAll(".navbar-nav .nav-link");

    navLinks.forEach(link => {
        link.addEventListener("click", function (event) {
            event.preventDefault();

            // Get the status to filter from the clicked link
            const status = this.getAttribute("href").split("=")[1] || "";

            // Filter table rows
            rows.forEach(row => {
                const rowStatus = row.getAttribute("data-status");
                if (status === "" || rowStatus === status) {
                    row.style.display = ""; // Show matching rows
                } else {
                    row.style.display = "none"; // Hide non-matching rows
                }
            });
        });
    });
});




