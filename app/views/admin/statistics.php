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
        $labels[] = $sales['method'];           // Phương thức thanh toán
        $revenues[] = $sales['revenue'];
    } elseif ($timePeriod === 'product') {
        $labels[] = $sales['product_name'];     // Tên sản phẩm
        $revenues[] = $sales['revenue'];
    } else {
        $labels[] = $sales['date'];             // Ngày (hoặc tuần, tháng, năm)
        $revenues[] = $sales['revenue'];
    }
}
?>

<h1 class="text-4xl font-extrabold text-center my-10 text-blue-700 drop-shadow-lg">Sales Statistics</h1>

<!-- Form chọn khoảng thời gian -->
<form method="POST" class="text-center mb-6">
    <!-- CSRF Token -->
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
    <label for="time_period" class="mr-2 text-lg">Select Time Period:</label>
    <select name="time_period" id="time_period" onchange="this.form.submit()" class="p-2 border rounded">
        <option value="daily" <?= $timePeriod === 'daily' ? 'selected' : '' ?>>Daily</option>
        <option value="monthly" <?= $timePeriod === 'monthly' ? 'selected' : '' ?>>Monthly</option>
        <option value="weekly" <?= $timePeriod === 'weekly' ? 'selected' : '' ?>>Weekly</option>
        <option value="yearly" <?= $timePeriod === 'yearly' ? 'selected' : '' ?>>Yearly</option>
        <option value="payment_method" <?= $timePeriod === 'payment_method' ? 'selected' : '' ?>>By Payment Method</option>
        <option value="product" <?= $timePeriod === 'product' ? 'selected' : '' ?>>By Product</option>
    </select>
</form>

<!-- Bảng thống kê doanh thu và Biểu đồ -->
<div class="container mx-auto p-6 bg-white shadow-xl rounded-lg mb-4 min-w-10/12">

    <!-- Bảng thống kê doanh thu -->
    <div class="overflow-x-auto mb-6">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-md">
            <thead>
                <tr class="bg-gray-100 text-gray-800 text-center">
                    <?php if ($timePeriod === 'payment_method'): ?>
                        <th class="px-4 py-3 border-b">Payment Method</th>
                    <?php elseif ($timePeriod === 'product'): ?>
                        <th class="px-4 py-3 border-b">Product Name</th>
                    <?php else: ?>
                        <th class="px-4 py-3 border-b">Date</th>
                    <?php endif; ?>
                    <th class="px-4 py-3 border-b">Total Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($salesData)): ?>
                    <?php foreach ($salesData as $sales): ?>
                        <tr class="hover:bg-gray-50">
                            <?php if ($timePeriod === 'payment_method'): ?>
                                <td class="px-4 py-3 border-b text-center"><?= htmlspecialchars($sales['method']) ?></td>
                            <?php elseif ($timePeriod === 'product'): ?>
                                <td class="px-4 py-3 border-b text-center"><?= htmlspecialchars($sales['product_name']) ?></td>
                            <?php else: ?>
                                <td class="px-4 py-3 border-b text-center"><?= htmlspecialchars($sales['date']) ?></td>
                            <?php endif; ?>
                            <td class="px-4 py-3 border-b text-center text-green-600 font-bold">
                                $<?= number_format($sales['revenue'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="px-4 py-3 border-b text-center text-gray-500">No sales data available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Biểu đồ doanh thu -->
    <div class="mt-6">
        <canvas id="salesChart" class="w-full max-h-96"></canvas>
    </div>
</div>

<div class="text-center mb-4">
    <button type="button" class="inline-block bg-green-500 text-white px-5 py-2 rounded-full hover:bg-purple-600 transition-all duration-200"
        onclick="window.location.href='/admin/list'">Back to Admin</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Lấy dữ liệu từ PHP và truyền vào biểu đồ
    var labels = <?php echo json_encode($labels); ?>;
    var revenues = <?php echo json_encode($revenues); ?>;

    // Thiết lập biểu đồ
    var ctx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(ctx, {
        type: 'bar', // Loại biểu đồ: 'bar', 'line', 'pie', v.v.
        data: {
            labels: labels, // Nhãn cho trục X (ngày, phương thức thanh toán, sản phẩm)
            datasets: [{
                label: 'Total Sales ($)',
                data: revenues, // Dữ liệu doanh thu
                backgroundColor: 'rgba(75, 192, 192, 0.2)', // Màu nền của thanh
                borderColor: 'rgba(75, 192, 192, 1)', // Màu viền của thanh
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1000 // Điều chỉnh bước nhảy cho trục Y
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
</script>