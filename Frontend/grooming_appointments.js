document.addEventListener("DOMContentLoaded", function () {
  fetchAppointments();
});

function fetchAppointments() {
  fetch("http://192.168.1.65/backend/fetch_all_appointments.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        displayAppointments(data.appointments);
      } else {
        alert("No appointments found.");
      }
    })
    .catch((error) => console.error("Error fetching data:", error));
}

function displayAppointments(appointments) {
  const tableBody = document.getElementById("appointmentsTableBody");
  tableBody.innerHTML = ""; // Clear existing data

  if (appointments.length === 0) {
    tableBody.innerHTML = `<tr><td colspan="7" style="text-align: center;">No appointments</td></tr>`;
    return;
  }

  appointments.forEach((appointment) => {
    const isEditable = appointment.status === "Pending"; // Only "Pending" appointments can be approved/declined
    const row = `
      <tr>
          <td>${appointment.name}</td>
          <td>${appointment.pet_name} (${appointment.pet_breed})</td>
          <td>${appointment.service_name}</td>
          <td>${formatDateTime(appointment.appointment_date, appointment.appointment_time)}</td>
          <td>₱${appointment.price}</td>
          <td><span class="${getStatusClass(appointment.status)}">${appointment.status}</span></td>
          <td>
              ${isEditable ? `
                <button class="approve-btn" onclick="updateStatus(${appointment.id}, 'Approved')">Approve</button>
                <button class="decline-btn" onclick="updateStatus(${appointment.id}, 'Declined')">Decline</button>
              ` : ""}
              <div class="dropdown">
                  <button class="dropdown-btn" onclick="toggleDropdown(event)">⋮</button>
                  <div class="dropdown-content">
                      <a href="#" onclick="viewDetails(${appointment.id})">View Details</a>
                      <a href="#" onclick="removeAppointment(${appointment.id})">Remove</a>
                  </div>
              </div>
          </td>
      </tr>
    `;
    tableBody.innerHTML += row;
  });
}

function getStatusClass(status) {
  switch (status) {
    case "Pending":
      return "status-pending";
    case "Approved":
      return "status-approved";
    case "Declined":
      return "status-declined";
    default:
      return "";
  }
}

function updateStatus(appointmentId, newStatus) {
  fetch("http://192.168.1.65/backend/update_appointment_status.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ appointment_id: appointmentId, status: newStatus }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Status updated successfully!");
        fetchAppointments(); // Refresh the table
      } else {
        alert("Error updating status.");
      }
    })
    .catch((error) => console.error("Error updating status:", error));
}
