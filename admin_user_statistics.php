<?php
$pageTitle = "User Statistics";
require_once 'admin_header.php';
require_once 'admin_db_connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- 1. User Growth: Monthly Registrations ---
$monthly_growth = [];
$sql_growth = "SELECT DATE_FORMAT(Add_Time, '%Y-%m') as month, COUNT(*) as users_added
               FROM user_file
               GROUP BY month
               ORDER BY month";
$result_growth = $conn->query($sql_growth);
if ($result_growth) {
    while ($row = $result_growth->fetch_assoc()) {
        $monthly_growth[] = $row;
    }
}



?>

<h1>User Statistics</h1>



<!-- 1. User Growth Over Time -->
<h2>User Growth Over Time</h2>
<canvas id="growthChart" style="max-width: 700px;"></canvas>

<!-- 2. Monthly Additions Table -->
<h3 style="margin-top: 2rem;" >Users Added Per Month</h3>
<table>
    <thead>
        <tr>
            <th>Month</th>
            <th>Users Added</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($monthly_growth as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['month']); ?></td>
                <td><?php echo htmlspecialchars($row['users_added']); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>



<!-- Chart.js for Growth Chart -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const growthCtx = document.getElementById('growthChart').getContext('2d');
    const growthChart = new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($monthly_growth, 'month')); ?>,
            datasets: [{
                label: 'Cumulative Users',
                data: (function() {
                    const monthly = <?php echo json_encode(array_column($monthly_growth, 'users_added')); ?>;
                    let cumulative = [], total = 0;
                    for (let i = 0; i < monthly.length; i++) {
                        total += parseInt(monthly[i]);
                        cumulative.push(total);
                    }
                    return cumulative;
                })(),
                borderColor: '#0A74DA',
                backgroundColor: 'rgba(10,116,218,0.2)',
                fill: true,
                tension: 0.2
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>

<?php require_once 'admin_footer.php'; ?>
