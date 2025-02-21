document.addEventListener("DOMContentLoaded", function () {
    fetch("http://192.168.137.239/backend/fetch_grooming_appointments.php")
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAppointments(data.appointments);
            } else {
                document.getElementById("groomingAppointmentsContainer").innerHTML = "<p>No grooming appointments found.</p>";
            }
        })
        .catch(error => console.error("Error fetching grooming appointments:", error));
});

function displayAppointments(appointments) {
    const container = document.getElementById("groomingAppointmentsContainer");
    container.innerHTML = "";

    appointments.forEach(appointment => {
        let statusBadge = `<span class="status pending">Pending</span>`;
        if (appointment.status === "Approved") {
            statusBadge = `<span class="status approved">Approved</span>`;
        } else if (appointment.status === "Declined") {
            statusBadge = `<span class="status declined">Declined</span>`;
        }

        const appointmentElement = document.createElement("div");
        appointmentElement.className = "appointment-item";
        appointmentElement.innerHTML = `
            <div class="appointment-image"></div>
            <div class="appointment-details">
                <h3>${appointment.groom_type}</h3>
                <p><strong>Customer:</strong> ${appointment.name}</p>
                <p><strong>Pet:</strong> ${appointment.pet_name} (${appointment.pet_breed})</p>
                <p><strong>Date:</strong> ${appointment.appointment_date}</p>
            </div>
            ${statusBadge}
            <button class="view-details" onclick='showGroomDetails(${JSON.stringify(appointment)})'>View Details</button>
        `;
        container.appendChild(appointmentElement);
    });
}

function updateGroomingStatus(appointmentId, status) {
    fetch("http://192.168.137.239/backend/approve_grooming_appointment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ appointment_id: appointmentId, status: status })
    })
    .then(() => location.reload())
    .catch(error => console.error("Error:", error));
}



function showGroomDetails(appointment) {
    document.getElementById("modalCustomerName").innerText = appointment.name || "N/A";
    document.getElementById("modalCustomerAddress").innerText = appointment.address || "N/A";
    document.getElementById("modalCustomerPhone").innerText = appointment.phone_number || "N/A";
    document.getElementById("modalPetName").innerText = appointment.pet_name || "N/A";
    document.getElementById("modalPetBreed").innerText = appointment.pet_breed || "N/A";
    document.getElementById("modalGroomType").innerText = appointment.groom_type || "N/A";
    document.getElementById("modalNotes").innerText = appointment.notes || "N/A";
    document.getElementById("modalDate").innerText = appointment.appointment_date || "N/A";
    document.getElementById("appointmentModal").style.display = "flex";

    document.getElementById("approveBtn").onclick = function() { updateGroomingStatus(appointment.id, "Approved"); };
    document.getElementById("declineBtn").onclick = function() { updateGroomingStatus(appointment.id, "Declined"); };
}

function approveGroomingAppointment() {
    const appointmentId = document.getElementById("appointmentModal").getAttribute("data-id");

    fetch("http://192.168.137.239/backend/approve_grooming_appointment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ appointment_id: appointmentId, status: "Approved" })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Appointment Approved!");
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error:", error));
}

// ✅ Function to decline an appointment
function declineGroomingAppointment() {
    const appointmentId = document.getElementById("appointmentModal").getAttribute("data-id");

    fetch("http://192.168.137.239/backend/approve_grooming_appointment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ appointment_id: appointmentId, status: "Declined" })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Appointment Declined.");
            location.reload();
        } else {
            alert("Error: " + data.message);
        }
    })
    .catch(error => console.error("Error:", error));
}

function closeModal() {
    document.getElementById("appointmentModal").style.display = "none";
}

function updateGroomingStatus(appointmentId, status) {
    fetch("http://192.168.137.239/backend/approve_grooming_appointment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ appointment_id: appointmentId, status: status })
    })
    .then(() => location.reload())
    .catch(error => console.error("Error:", error));
}

const modal = document.querySelector(".modal");
const openModalBtns = document.querySelectorAll(".view-details-btn"); // Change this selector based on your buttons
const closeModalBtns = document.querySelectorAll(".cancel-btn, .decline-btn, .approve-btn"); // Include all possible close buttons

// Function to open the modal
function openModal() {
  modal.style.display = "flex";
  document.body.classList.add("modal-open"); // Prevent scrolling
}

// Function to close the modal
function closeModal() {
  modal.style.display = "none";
  document.body.classList.remove("modal-open"); // Allow scrolling again
}

// Attach event listeners to open modal buttons
openModalBtns.forEach(btn => {
  btn.addEventListener("click", openModal);
});

// Attach event listeners to close modal buttons
closeModalBtns.forEach(btn => {
  btn.addEventListener("click", closeModal);
});

// Close modal if clicking outside modal content
modal.addEventListener("click", (event) => {
  if (event.target === modal) {
    closeModal();
  }
});
