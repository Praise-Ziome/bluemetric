<?php
// 🔒 Session protection — only allow logged-in users
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BlueMetrics Dashboard</title>

  <!-- TailwindCSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

  <!-- Chart.js for graph rendering -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="bg-gray-100 text-gray-800">

  <!-- 💧 Sidebar -->
  <aside class="fixed left-0 top-0 h-full w-60 bg-blue-700 text-white flex flex-col justify-between shadow-lg">
    <div>
      <h1 class="text-2xl font-bold p-4 text-center">💧 BlueMetrics</h1>
      <nav class="px-4">
        <button onclick="showView('live')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-blue-600 mt-2">Live Dashboard</button>
        <button onclick="showView('analytics')" class="w-full text-left px-3 py-2 rounded-lg hover:bg-blue-600 mt-2">Analytics / Manual Entry</button>
      </nav>
    </div>

    <!-- 🚪 Logout button -->
<button onclick="window.location.href='logout.php'" class="w-full text-left px-3 py-2 rounded-lg hover:bg-red-600 bg-red-500 mt-4 mb-6 text-center">
  Logout
</button>
  </aside>

  <!-- 🧠 Main Content -->
  <main class="ml-60 p-6">
    <!-- Header -->
    <header class="bg-white shadow p-4 rounded-lg mb-4">
      <h2 class="text-2xl font-semibold text-blue-700">💧 BlueMetrics Real-Time Dashboard</h2>
      <p class="text-sm text-gray-500">Monitoring live pH, Temperature, and Turbidity data</p>
    </header>

    <!-- Live Dashboard View -->
    <section id="live" class="view">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
        <div class="bg-white rounded-xl p-4 shadow-md text-center">
          <h2 class="text-gray-500 text-sm">pH Level</h2>
          <p id="ph-value" class="text-3xl font-bold text-blue-600">--</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-md text-center">
          <h2 class="text-gray-500 text-sm">Temperature (°C)</h2>
          <p id="temp-value" class="text-3xl font-bold text-green-600">--</p>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-md text-center">
          <h2 class="text-gray-500 text-sm">Turbidity (NTU)</h2>
          <p id="turb-value" class="text-3xl font-bold text-yellow-600">--</p>
        </div>
      </div>

      <!-- Charts -->
      <div class="bg-white rounded-xl p-4 shadow-md mb-4">
        <h3 class="text-blue-700 font-semibold mb-2">Live Water Quality Trends</h3>
        <canvas id="liveChart"></canvas>
      </div>
    </section>

    <!-- Analytics / Manual Entry View -->
    <section id="analytics" class="view hidden">
      <div class="bg-white rounded-xl p-4 shadow-md">
        <h3 class="text-blue-700 font-semibold mb-2">Manual Data Entry (Testing)</h3>
        <form id="manualForm" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <input type="number" step="0.01" id="phInput" placeholder="pH Level" class="border p-2 rounded" required>
          <input type="number" step="0.01" id="tempInput" placeholder="Temperature (°C)" class="border p-2 rounded" required>
          <input type="number" step="0.01" id="turbInput" placeholder="Turbidity (NTU)" class="border p-2 rounded" required>
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Add Reading</button>
        </form>
      </div>

      <div class="bg-white rounded-xl p-4 shadow-md mt-6">
        <h3 class="text-blue-700 font-semibold mb-2">Manual Entries Chart</h3>
        <canvas id="manualChart"></canvas>
      </div>
    </section>

    <!-- Footer -->
    <footer class="text-center text-sm text-gray-500 mt-6">
      © <script>document.write(new Date().getFullYear());</script> Praise R. Ziome
    </footer>
  </main>

  <!-- ============================= -->
  <!--  JAVASCRIPT SECTION           -->
  <!-- ============================= -->
  <script>
    // ====== View Switching ======
    function showView(view) {
      document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
      document.getElementById(view).classList.remove('hidden');
    }

    // ====== Chart Setup ======
    const ctxLive = document.getElementById('liveChart').getContext('2d');
    const liveChart = new Chart(ctxLive, {
      type: 'line',
      data: {
        labels: [],
        datasets: [
          { label: 'pH', data: [], borderColor: '#2563EB', fill: false },
          { label: 'Temperature', data: [], borderColor: '#10B981', fill: false },
          { label: 'Turbidity', data: [], borderColor: '#F59E0B', fill: false }
        ]
      },
      options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // ====== Fetch Live Data from get_data.php ======
    let lastTimestamp = null;

    async function fetchSensorData() {
      try {
        const res = await fetch('./get_data.php');
        const data = await res.json();
        const latest = data[data.length - 1];

        if (!latest) return;

        // Avoid duplicate timestamps
        if (lastTimestamp !== latest.timestamp) {
          document.getElementById('ph-value').innerText = parseFloat(latest.ph).toFixed(2);
          document.getElementById('temp-value').innerText = parseFloat(latest.temperature).toFixed(2);
          document.getElementById('turb-value').innerText = parseFloat(latest.turbidity).toFixed(2);

          liveChart.data.labels.push(latest.timestamp);
          liveChart.data.datasets[0].data.push(latest.ph);
          liveChart.data.datasets[1].data.push(latest.temperature);
          liveChart.data.datasets[2].data.push(latest.turbidity);

          if (liveChart.data.labels.length > 20) {
            liveChart.data.labels.shift();
            liveChart.data.datasets.forEach(ds => ds.data.shift());
          }

          liveChart.update();
          lastTimestamp = latest.timestamp;
        }

      } catch (err) {
        console.error('Error fetching data:', err);
      }
    }

    // Auto-refresh every 5 seconds
    setInterval(fetchSensorData, 5000);
    fetchSensorData();

    // ====== Manual Entry (Testing Analytics) ======
    const ctxManual = document.getElementById('manualChart').getContext('2d');
    const manualChart = new Chart(ctxManual, {
      type: 'line',
      data: {
        labels: [],
        datasets: [
          { label: 'pH', data: [], borderColor: '#2563EB', fill: false },
          { label: 'Temperature', data: [], borderColor: '#10B981', fill: false },
          { label: 'Turbidity', data: [], borderColor: '#F59E0B', fill: false }
        ]
      },
      options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    document.getElementById('manualForm').addEventListener('submit', e => {
      e.preventDefault();
      const ph = parseFloat(document.getElementById('phInput').value);
      const temp = parseFloat(document.getElementById('tempInput').value);
      const turb = parseFloat(document.getElementById('turbInput').value);
      const timestamp = new Date().toLocaleTimeString();

      manualChart.data.labels.push(timestamp);
      manualChart.data.datasets[0].data.push(ph);
      manualChart.data.datasets[1].data.push(temp);
      manualChart.data.datasets[2].data.push(turb);
      manualChart.update();

      e.target.reset();
    });
  </script>
</body>
</html>