<?php

// Kiểm tra quyền admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /login");
    exit();
}

// Tạo token CSRF nếu chưa tồn tại
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Kiểm tra xem có giá trị time_period từ POST không
$timePeriod = isset($_POST['time_period']) ? $_POST['time_period'] : 'daily';

// Khởi tạo OrderController và lấy thời gian lựa chọn
$statisticsController = new OrderController($conn);

// Gọi phương thức getSalesStatistics với timePeriod đã chọn
$salesData = $statisticsController->getSalesStatistics($timePeriod);

// Chuyển đổi dữ liệu PHP thành định dạng mà Chart.js có thể sử dụng
$labels = [];
$revenues = [];

foreach ($salesData as $sales) {
    if ($timePeriod === 'payment_method') {
        $labels[] = $sales['method'];
        $revenues[] = $sales['revenue'];
    } elseif ($timePeriod === 'product') {
        $labels[] = $sales['product_name'];
        $revenues[] = $sales['revenue'];
    } else {
        $labels[] = $sales['date'];
        $revenues[] = $sales['revenue'];
    }
}
?>

<h1 class="text-4xl font-extrabold text-center my-10 text-blue-700 drop-shadow-lg">📊 Sales Statistics</h1>

<!-- Form chọn khoảng thời gian -->
<form method="POST" class="text-center mb-6">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <label for="time_period" class="mr-2 text-lg font-semibold">📅 Select Time Period:</label>
    <select name="time_period" id="time_period" onchange="this.form.submit()"
        class="p-2 border border-gray-300 rounded-lg focus:ring focus:ring-blue-300">
        <option value="daily" <?= $timePeriod === 'daily' ? 'selected' : '' ?>>Daily</option>
        <option value="monthly" <?= $timePeriod === 'monthly' ? 'selected' : '' ?>>Monthly</option>
        <option value="yearly" <?= $timePeriod === 'yearly' ? 'selected' : '' ?>>Yearly</option>
        <option value="payment_method" <?= $timePeriod === 'payment_method' ? 'selected' : '' ?>>Payment Method</option>
        <option value="product" <?= $timePeriod === 'product' ? 'selected' : '' ?>>Product</option>
    </select>
</form>

<!-- Thống kê tổng quan -->
<div class="container mx-auto p-6 bg-white shadow-xl rounded-lg mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-white">
        <div class="bg-blue-600 p-6 rounded-xl shadow-lg text-center">
            <h3 class="text-lg font-bold">💰 Total Sales</h3>
            <p class="text-3xl font-semibold">$<?= number_format(array_sum($revenues), 2) ?></p>
        </div>
        <div class="bg-green-600 p-6 rounded-xl shadow-lg text-center">
            <h3 class="text-lg font-bold">📦 Total Orders</h3>
            <p class="text-3xl font-semibold"><?= count($salesData) ?></p>
        </div>
        <div class="bg-yellow-500 p-6 rounded-xl shadow-lg text-center">
            <h3 class="text-lg font-bold">🏆 Top Product</h3>
            <p class="text-2xl font-semibold truncate"> <?= $salesData[0]['product_name'] ?? 'N/A' ?></p>
        </div>
        <div class="bg-red-500 p-6 rounded-xl shadow-lg text-center">
            <h3 class="text-lg font-bold">📈 Highest Revenue</h3>
            <p class="text-3xl font-semibold">$<?= number_format(max($revenues), 2) ?></p>
        </div>
    </div>

    <!-- Bảng thống kê doanh thu -->
    <div class="overflow-x-auto mb-6 mt-6">
        <table class="min-w-full mx-auto bg-white border border-gray-200 rounded-lg shadow-md">
            <thead class="bg-gray-200 text-gray-800 text-center">
                <tr>
                    <th class="px-6 py-3 border-b">Category</th>
                    <th class="px-6 py-3 border-b">Total Quantity</th>
                    <th class="px-6 py-3 border-b">Total Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($salesData)): ?>
                    <?php foreach ($salesData as $sales): ?>
                        <tr class="hover:bg-gray-100">
                            <td class="px-6 py-4 border-b text-center font-medium">
                                <?= htmlspecialchars($sales[$timePeriod === 'payment_method' ? 'method' : ($timePeriod === 'product' ? 'product_name' : 'date')]) ?>
                            </td>
                            <td class="px-6 py-4 border-b text-center text-blue-600 font-bold">
                                <?= number_format($sales['total_quantity']) ?>
                            </td>
                            <td class="px-6 py-4 border-b text-center text-green-600 font-bold">
                                $<?= number_format($sales['revenue'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="px-6 py-4 border-b text-center text-gray-500">No sales data available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Biểu đồ doanh thu -->
    <div class="mt-6">
        <canvas id="salesChart" class="min-w-full mx-auto max-h-96"></canvas>
    </div>
</div>

<!-- Nút quay lại -->
<div class="text-center mb-6">
    <button type="button" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-purple-600 transition-all duration-200"
        onclick="window.location.href='/admin/list'">⬅ Back to Admin</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    var labels = <?= json_encode($labels); ?>;
    var revenues = <?= json_encode($revenues); ?>;

    var ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Sales ($)',
                data: revenues,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                hoverBackgroundColor: 'rgba(255, 99, 132, 0.6)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
</script>