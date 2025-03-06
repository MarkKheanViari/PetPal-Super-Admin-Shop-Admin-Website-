document.addEventListener("DOMContentLoaded", function () {
    fetchAnalyticsData();
  });
  
  async function fetchAnalyticsData() {
    try {
      // Fetch your analytics data (modify the URL if needed)
      const response = await fetch("http://192.168.168.203/backend/Frontend/Superadmin/superadmin_stats.php");
      const data = await response.json();
  
      // Update statistic cards
      document.getElementById("totalUsers").textContent = data.totalUsers ?? "N/A";
      document.getElementById("activeShopOwners").textContent = data.activeShopOwners ?? "N/A";
      document.getElementById("activeCustomers").textContent = data.activeCustomers ?? "N/A";
      document.getElementById("pendingProducts").textContent = data.pendingProducts ?? "N/A";
  
      // Render charts using data from the API
      // For example, assume these properties exist in the API response:
      // data.registrationMonths (an array of month names)
      // data.registrationCounts (an array of user counts)
      // data.productStatusLabels (an array of product status labels)
      // data.productStatusCounts (an array of counts for each product status)
      renderUserTrendChart(data.registrationMonths, data.registrationCounts);
      renderProductChart(data.productStatusLabels, data.productStatusCounts);
    } catch (error) {
      console.error("Error fetching analytics data:", error);
    }
  }
  
  function renderUserTrendChart(labels, dataValues) {
    const ctx = document.getElementById("userTrendChart").getContext("2d");
    new Chart(ctx, {
      type: "line",
      data: {
        labels: labels,
        datasets: [{
          label: "Registrations",
          data: dataValues,
          backgroundColor: "rgba(255, 159, 64, 0.2)",
          borderColor: "rgba(255, 159, 64, 1)",
          borderWidth: 2,
          fill: true,
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  }
  
  function renderProductChart(labels, dataValues) {
    const ctx = document.getElementById("productChart").getContext("2d");
    new Chart(ctx, {
      type: "doughnut",
      data: {
        labels: labels,
        datasets: [{
          data: dataValues,
          backgroundColor: [
            "rgba(75, 192, 192, 0.8)",
            "rgba(255, 99, 132, 0.8)",
            "rgba(255, 205, 86, 0.8)"
          ],
          borderWidth: 1,
        }]
      },
      options: {
        responsive: true,
      }
    });
  }
  