<?php
require_once('../../includes/auth.php');
checkAdminAuth();
require_once('../../config/database.php');

// --- Filter logic ---
$filterType = $_GET['filterType'] ?? 'day';
$filterDay = $_GET['day'] ?? date('Y-m-d');
$filterMonth = $_GET['month'] ?? date('Y-m');
$filterStart = $_GET['start'] ?? '';
$filterEnd = $_GET['end'] ?? '';

$where = '';
$params = [];

if ($filterType === 'day') {
    $where = "TRUNC(o.created_at) = TO_DATE(:filterDay, 'YYYY-MM-DD')";
    $params[':filterDay'] = $filterDay;
} elseif ($filterType === 'month') {
    $where = "TO_CHAR(o.created_at, 'YYYY-MM') = :filterMonth";
    $params[':filterMonth'] = $filterMonth;
} elseif ($filterType === 'range' && $filterStart && $filterEnd) {
    $where = "TRUNC(o.created_at) BETWEEN TO_DATE(:filterStart, 'YYYY-MM-DD') AND TO_DATE(:filterEnd, 'YYYY-MM-DD')";
    $params[':filterStart'] = $filterStart;
    $params[':filterEnd'] = $filterEnd;
} else {
    $where = "1=1";
}

// Filtered orders and summary
$orders_filtered = [];
$filter = ['ORDER_COUNT' => 0, 'TOTAL_SALES' => 0.0];

$orders_sql = "SELECT o.id, u.name AS user_name, o.total_amount, o.status, o.created_at
               FROM orders o
               LEFT JOIN users u ON o.user_id = u.id
               WHERE $where
               ORDER BY o.created_at DESC";
$orders_stmt = oci_parse($conn, $orders_sql);
foreach ($params as $key => $val) {
    oci_bind_by_name($orders_stmt, $key, $params[$key]);
}
oci_execute($orders_stmt);
while ($row = oci_fetch_assoc($orders_stmt)) {
    $orders_filtered[] = $row;
}
oci_free_statement($orders_stmt);

$filter_sql = "SELECT COUNT(*) AS order_count, NVL(SUM(o.total_amount),0) AS total_sales
               FROM orders o
               LEFT JOIN users u ON o.user_id = u.id
               WHERE $where";
$filter_stmt = oci_parse($conn, $filter_sql);
foreach ($params as $key => $val) {
    oci_bind_by_name($filter_stmt, $key, $params[$key]);
}
oci_execute($filter_stmt);
$filter = oci_fetch_assoc($filter_stmt);
oci_free_statement($filter_stmt);

// Daily report
$daily_sql = "SELECT COUNT(*) AS order_count, NVL(SUM(total_amount),0) AS total_sales
              FROM orders
              WHERE TRUNC(created_at) = TRUNC(SYSDATE)";
$daily_stmt = oci_parse($conn, $daily_sql);
oci_execute($daily_stmt);
$daily = oci_fetch_assoc($daily_stmt);
oci_free_statement($daily_stmt);

// Monthly report
$monthly_sql = "SELECT COUNT(*) AS order_count, NVL(SUM(total_amount),0) AS total_sales
                FROM orders
                WHERE TO_CHAR(created_at, 'YYYYMM') = TO_CHAR(SYSDATE, 'YYYYMM')";
$monthly_stmt = oci_parse($conn, $monthly_sql);
oci_execute($monthly_stmt);
$monthly = oci_fetch_assoc($monthly_stmt);
oci_free_statement($monthly_stmt);

// Fetch daily orders
$orders_today_sql = "SELECT o.id, u.name AS user_name, o.total_amount, o.status, o.created_at
                     FROM orders o
                     LEFT JOIN users u ON o.user_id = u.id
                     WHERE TRUNC(o.created_at) = TRUNC(SYSDATE)
                     ORDER BY o.created_at DESC";
$orders_today_stmt = oci_parse($conn, $orders_today_sql);
oci_execute($orders_today_stmt);
$orders_today = [];
while ($row = oci_fetch_assoc($orders_today_stmt)) {
    $orders_today[] = $row;
}
oci_free_statement($orders_today_stmt);

// Fetch monthly orders
$orders_month_sql = "SELECT o.id, u.name AS user_name, o.total_amount, o.status, o.created_at
                     FROM orders o
                     LEFT JOIN users u ON o.user_id = u.id
                     WHERE TO_CHAR(o.created_at, 'YYYYMM') = TO_CHAR(SYSDATE, 'YYYYMM')
                     ORDER BY o.created_at DESC";
$orders_month_stmt = oci_parse($conn, $orders_month_sql);
oci_execute($orders_month_stmt);
$orders_month = [];
while ($row = oci_fetch_assoc($orders_month_stmt)) {
    $orders_month[] = $row;
}
oci_free_statement($orders_month_stmt);

oci_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily and Monthly Reports - Admin Dashboard</title>
    <link rel="stylesheet" href="../../Assets/css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .report-section {
            background: #fff;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            /* Animation */
            opacity: 0;
            transform: translateY(30px);
            animation: fadeInUp 0.7s ease-out 0.2s forwards;
        }
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .badge.bg-info {
            animation: bounce 1s infinite alternate;
        }
        @keyframes bounce {
            0% { transform: translateY(0);}
            100% { transform: translateY(-8px);}
        }
        .report-title {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .report-summary {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .summary-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem 2rem;
            flex: 1;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .summary-title {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
        }
        .summary-value {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
        }
        .summary-sales {
            font-size: 1.1rem;
            color: #27ae60;
            margin-top: 0.25rem;
        }
        .table-responsive {
            margin-top: 1.5rem;
        }
        .dashboard-table th, .dashboard-table td {
            vertical-align: middle;
        }
        .chart-container {
            background: #fff;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }
        .chart-title {
            font-size: 1.2rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            text-align: center;
        }
        .filter-form {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }
        .filter-form label {
            font-weight: 500;
            margin-right: 10px;
        }
        .filter-form .form-control,
        .filter-form .btn {
            min-width: 120px;
        }
        @media (max-width: 768px) {
            .report-summary { flex-direction: column; gap: 1rem; }
            .chart-container, .filter-form { padding: 1rem; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <i class="fas fa-utensils"></i> QuickFeast Delivery
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="../dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a href="../products/manage_products.php"><i class="fas fa-boxes"></i> Products</a></li>
                <li class="nav-item"><a href="../orders/manage_orders.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
                <li class="nav-item"><a href="../categories/manage_categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                <li class="nav-item active"><a href="manage_Daily_and_monthly.php"><i class="fas fa-tags"></i> Daily and Monthly Reports</a></li>
                <li class="nav-item"><a href="../../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        <!-- Main Content -->
        <div class="main-content">
            <div class="report-section">
                <div class="report-title">
                    <i class="fas fa-chart-line"></i> Daily and Monthly Sales Report
                </div>
                <form class="filter-form row g-3 align-items-center" method="get" id="filterForm">
                    <div class="col-auto">
                        <label for="filterType" class="col-form-label"><i class="fas fa-filter"></i> Filter By:</label>
                        <span class="badge bg-info text-dark ms-2">Apply Filter By</span>
                    </div>
                    <div class="col-auto">
                        <select class="form-control" id="filterType" name="filterType" onchange="toggleDateInputs()">
                            <option value="day" <?php if($filterType==='day') echo 'selected'; ?>>Day</option>
                            <option value="month" <?php if($filterType==='month') echo 'selected'; ?>>Month</option>
                            <option value="range" <?php if($filterType==='range') echo 'selected'; ?>>Date Range</option>
                        </select>
                    </div>
                    <div class="col-auto<?php if($filterType!=='day') echo ' d-none'; ?>" id="dayInput">
                        <input type="date" class="form-control" name="day" value="<?php echo htmlspecialchars($filterDay); ?>">
                    </div>
                    <div class="col-auto<?php if($filterType!=='month') echo ' d-none'; ?>" id="monthInput">
                        <input type="month" class="form-control" name="month" value="<?php echo htmlspecialchars($filterMonth); ?>">
                    </div>
                    <div class="col-auto<?php if($filterType!=='range') echo ' d-none'; ?>" id="rangeInput">
                        <input type="date" class="form-control" name="start" value="<?php echo htmlspecialchars($filterStart); ?>" placeholder="Start Date">
                        <span class="mx-2">to</span>
                        <input type="date" class="form-control" name="end" value="<?php echo htmlspecialchars($filterEnd); ?>" placeholder="End Date">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Apply</button>
                    </div>
                </form>
                <div class="report-summary">
                    <div class="summary-card">
                        <div class="summary-title">Filtered Orders</div>
                        <div class="summary-value"><?php echo $filter['ORDER_COUNT']; ?></div>
                        <div class="summary-sales">Total Sales: $<?php echo number_format($filter['TOTAL_SALES'], 2); ?></div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-title">Today's Orders</div>
                        <div class="summary-value"><?php echo $daily['ORDER_COUNT']; ?></div>
                        <div class="summary-sales">Total Sales: $<?php echo number_format($daily['TOTAL_SALES'], 2); ?></div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-title">This Month's Orders</div>
                        <div class="summary-value"><?php echo $monthly['ORDER_COUNT']; ?></div>
                        <div class="summary-sales">Total Sales: $<?php echo number_format($monthly['TOTAL_SALES'], 2); ?></div>
                    </div>
                </div>
                <!-- Data Visualization Section -->
                <div class="chart-container">
                    <div class="chart-title"><i class="fas fa-chart-bar"></i> Orders & Sales Overview</div>
                    <canvas id="ordersSalesChart" height="120"></canvas>
                </div>
                <h4 class="mt-4 mb-3"><i class="fas fa-filter"></i> Filtered Orders</h4>
                <div class="table-responsive">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($orders_filtered) === 0): ?>
                                <tr><td colspan="5" class="text-center text-muted">No orders found for this filter.</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders_filtered as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['ID']); ?></td>
                                        <td><?php echo htmlspecialchars($order['USER_NAME']); ?></td>
                                        <td>$<?php echo number_format($order['TOTAL_AMOUNT'], 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php
                                                switch (strtolower($order['STATUS'])) {
                                                    case 'pending': echo 'status-pending'; break;
                                                    case 'processing': echo 'status-processing'; break;
                                                    case 'completed': echo 'status-success'; break;
                                                    default: echo 'status-pending';
                                                }
                                            ?>">
                                                <?php echo htmlspecialchars(ucfirst($order['STATUS'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['CREATED_AT']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <h4 class="mt-4 mb-3"><i class="fas fa-calendar-day"></i> Today's Orders</h4>
                <div class="table-responsive">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($orders_today) === 0): ?>
                                <tr><td colspan="5" class="text-center text-muted">No orders today.</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders_today as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['ID']); ?></td>
                                        <td><?php echo htmlspecialchars($order['USER_NAME']); ?></td>
                                        <td>$<?php echo number_format($order['TOTAL_AMOUNT'], 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php
                                                switch (strtolower($order['STATUS'])) {
                                                    case 'pending': echo 'status-pending'; break;
                                                    case 'processing': echo 'status-processing'; break;
                                                    case 'completed': echo 'status-success'; break;
                                                    default: echo 'status-pending';
                                                }
                                            ?>">
                                                <?php echo htmlspecialchars(ucfirst($order['STATUS'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['CREATED_AT']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <h4 class="mt-5 mb-3"><i class="fas fa-calendar-alt"></i> This Month's Orders</h4>
                <div class="table-responsive">
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($orders_month) === 0): ?>
                                <tr><td colspan="5" class="text-center text-muted">No orders this month.</td></tr>
                            <?php else: ?>
                                <?php foreach ($orders_month as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['ID']); ?></td>
                                        <td><?php echo htmlspecialchars($order['USER_NAME']); ?></td>
                                        <td>$<?php echo number_format($order['TOTAL_AMOUNT'], 2); ?></td>
                                        <td>
                                            <span class="status-badge <?php
                                                switch (strtolower($order['STATUS'])) {
                                                    case 'pending': echo 'status-pending'; break;
                                                    case 'processing': echo 'status-processing'; break;
                                                    case 'completed': echo 'status-success'; break;
                                                    default: echo 'status-pending';
                                                }
                                            ?>">
                                                <?php echo htmlspecialchars(ucfirst($order['STATUS'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['CREATED_AT']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data for visualization
        const ordersSalesData = {
            labels: ["Today", "This Month"],
            datasets: [
                {
                    label: "Orders",
                    data: [
                        <?php echo (int)$daily['ORDER_COUNT']; ?>,
                        <?php echo (int)$monthly['ORDER_COUNT']; ?>
                    ],
                    backgroundColor: "rgba(52, 152, 219, 0.7)",
                    borderColor: "rgba(52, 152, 219, 1)",
                    borderWidth: 1,
                    yAxisID: 'y-orders'
                },
                {
                    label: "Sales ($)",
                    data: [
                        <?php echo (float)$daily['TOTAL_SALES']; ?>,
                        <?php echo (float)$monthly['TOTAL_SALES']; ?>
                    ],
                    backgroundColor: "rgba(39, 174, 96, 0.7)",
                    borderColor: "rgba(39, 174, 96, 1)",
                    borderWidth: 1,
                    type: 'line',
                    yAxisID: 'y-sales'
                }
            ]
        };

        const ctx = document.getElementById('ordersSalesChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: ordersSalesData,
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top' },
                    title: { display: false }
                },
                scales: {
                    yOrders: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Orders' },
                        beginAtZero: true,
                        grid: { drawOnChartArea: false }
                    },
                    ySales: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Sales ($)' },
                        beginAtZero: true,
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });

        // Filter form JS
        function toggleDateInputs() {
            const type = document.getElementById('filterType').value;
            document.getElementById('dayInput').classList.toggle('d-none', type !== 'day');
            document.getElementById('monthInput').classList.toggle('d-none', type !== 'month');
            document.getElementById('rangeInput').classList.toggle('d-none', type !== 'range');
        }
        document.getElementById('filterType').addEventListener('change', toggleDateInputs);
        window.onload = toggleDateInputs;
    </script>
</body>
</html>
