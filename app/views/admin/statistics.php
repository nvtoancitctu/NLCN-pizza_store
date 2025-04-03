<?php

// Kiểm tra quyền admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: /login");
    exit();
}

// Kiểm tra xem có giá trị time_period từ POST không
$timePeriod = isset($_POST['time_period']) ? $_POST['time_period'] : 'daily';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $timePeriod = $_POST['time_period'] ?? 'daily';

    // Nếu người dùng bấm Export PDF
    if (isset($_POST['export_pdf'])) {
        header("Location: ../index.php?page=exportPDF&time_period=" . urlencode($timePeriod));
        exit;
    }
}

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
    } elseif ($timePeriod === 'status') {
        $labels[] = $sales['status'];
        $revenues[] = $sales['revenue'];
    } else {
        $labels[] = $sales['date'];
        $revenues[] = $sales['revenue'];
    }
}

?>

<!-- Hiển thị thông báo lỗi hoặc thành công nếu có -->
<?php
$message = '';
$messageType = ''; // Để xác định loại thông báo (error hay success)
if (!empty($_SESSION['error'])) {
    $message = $_SESSION['error'];
    $messageType = 'error';
    unset($_SESSION['error']);
} elseif (!empty($_SESSION['success'])) {
    $message = $_SESSION['success'];
    $messageType = 'success';
    unset($_SESSION['success']);
}
?>

<!-- Hiển thị thông báo -->
<?php if (!empty($message)): ?>
    <div class="fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 <?= $messageType === 'error' ? 'bg-red-100 border border-red-400 text-red-700' : 'bg-green-100 border border-green-400 text-green-700' ?>">
        <span><?= htmlspecialchars($message) ?></span>
        <button onclick="this.parentElement.remove()" class="ml-2 text-sm font-semibold">✕</button>
    </div>
<?php endif; ?>

<!-- Script tự động ẩn (đặt ở cuối trang hoặc ngoài vòng lặp) -->
<?php if (!empty($message)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const elements = document.querySelectorAll('.fixed');
                if (elements.length > 0) {
                    elements.forEach(element => element.remove());
                }
            }, 5000);
        });
    </script>
<?php endif; ?>

<h1 class="text-4xl font-extrabold text-center mt-10 text-blue-700 drop-shadow-lg">📊 Sales Statistics</h1>

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
        <option value="status" <?= $timePeriod === 'status' ? 'selected' : '' ?>>Order Status</option>
    </select>

    <button type="submit" name="export_pdf" class="bg-red-500 text-white p-3 rounded-lg hover:bg-red-700 transition-all duration-200 my-4 ml-2">
        📄 Export PDF
    </button>
</form>

<!-- Thống kê tổng quan -->
<div class="container mx-auto p-6 bg-white shadow-xl rounded-xl mb-6 border-2 border-yellow-300">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-white">
        <div class="bg-blue-600 p-6 rounded-xl shadow-sm text-center">
            <h3 class="text-lg font-bold">💰 Total Revenue</h3>
            <p class="text-3xl font-semibold">
                $<?= !empty($revenues) ? number_format(array_sum($revenues), 2) : '0.00' ?>
            </p>
        </div>

        <div class="bg-green-600 p-6 rounded-xl shadow-sm text-center">
            <h3 class="text-lg font-bold">📦 Total Orders</h3>
            <p class="text-3xl font-semibold">
                <?= !empty($salesData) ? array_sum(array_column($salesData, 'total_quantity')) : 0 ?>
            </p>
        </div>

        <div class="bg-yellow-500 p-6 rounded-xl shadow-sm text-center">
            <h3 class="text-lg font-bold">🏆 Top Category</h3>
            <p class="text-2xl font-semibold truncate">
                <?php
                if ($timePeriod === 'product') {
                    echo $salesData[0]['product_name'] ?? 'N/A';
                } elseif ($timePeriod === 'payment_method') {
                    echo $salesData[0]['method'] ?? 'N/A';
                } elseif ($timePeriod === 'status') {
                    echo $salesData[0]['status'] ?? 'N/A';
                } else {
                    echo 'N/A';
                }
                ?>
            </p>
        </div>

        <div class="bg-red-500 p-6 rounded-xl shadow-sm text-center">
            <h3 class="text-lg font-bold">📈 Highest Revenue</h3>
            <p class="text-3xl font-semibold">
                $<?= !empty($revenues) ? number_format(max($revenues), 2) : '0.00' ?>
            </p>
        </div>
    </div>

    <!-- Biểu đồ tỷ lệ doanh thu -->
    <div class="mt-6">
        <canvas id="salesPieChart" class="w-3/5 mx-auto max-h-96"></canvas>
    </div>

    <!-- Bảng thống kê doanh thu -->
    <div class="mb-6 mt-6">
        <table class="min-w-full mx-auto border border-gray-300 rounded-xl shadow-sm">
            <thead class="bg-blue-500 text-white text-center">
                <tr>
                    <th class="px-4 py-3 border-b border-gray-300">Category</th>
                    <th class="px-4 py-3 border-b border-gray-300">Total Quantity</th>
                    <th class="px-4 py-3 border-b border-gray-300">Total Sales</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (!empty($salesData)): ?>
                    <?php foreach ($salesData as $index => $sales): ?>
                        <tr class="<?= $index % 2 === 0 ? 'bg-gray-100' : 'bg-white' ?> hover:bg-gray-200">
                            <td class="px-4 py-3 text-center font-semibold text-gray-700">
                                <?= htmlspecialchars(
                                    $sales[$timePeriod === 'payment_method' ? 'method' : ($timePeriod === 'product' ? 'product_name' : ($timePeriod === 'status' ? 'status' : 'date'))]
                                ) ?>
                            </td>
                            <td class="px-4 py-3 text-center text-blue-600 font-bold">
                                <?= number_format($sales['total_quantity']) ?>
                            </td>
                            <td class="px-4 py-3 text-center text-green-600 font-bold">
                                $<?= number_format($sales['revenue'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-center text-gray-500">No sales data available</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<!-- Nút quay lại -->
<div class="text-center mb-6">
    <button type="button" class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-purple-600 transition-all duration-200"
        onclick="window.location.href='/admin'">⬅ Back to Admin</button>
</div>

<?php
// Đánh số thứ tự cho labels
$numberedLabels = array_map(function ($label, $index) {
    return ($index + 1) . ". " . $label;
}, $labels, array_keys($labels));
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>

<script>
    var ctxPie = document.getElementById('salesPieChart').getContext('2d');
    new Chart(ctxPie, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($numberedLabels); ?>, // Nhãn có số thứ tự
            datasets: [{
                data: <?= json_encode($revenues); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)', // Đỏ
                    'rgba(54, 162, 235, 0.5)', // Xanh dương
                    'rgba(255, 206, 86, 0.5)', // Vàng
                    'rgba(75, 192, 192, 0.5)', // Xanh ngọc
                    'rgba(153, 102, 255, 0.5)', // Tím
                    'rgba(255, 159, 64, 0.5)', // Cam
                    'rgba(46, 204, 113, 0.5)', // Xanh lá cây
                    'rgba(231, 76, 60, 0.5)', // Đỏ sậm
                    'rgba(241, 196, 15, 0.5)', // Vàng sáng
                    'rgba(52, 152, 219, 0.5)' // Xanh biển
                ],
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                },
                datalabels: {
                    color: '#000', // Màu chữ
                    font: {
                        size: 10
                    },
                    formatter: (value, context) => {
                        return context.dataIndex + 1; // Hiển thị số thứ tự
                    }
                }
            }
        },
        plugins: [ChartDataLabels] // Kích hoạt plugin hiển thị số
    });
</script>