// navbar đối với giao diện cửa sổ nhỏ
document.getElementById('navbar-toggler').addEventListener('click', function () {
    const menu = document.getElementById('mobile-menu');
    menu.classList.toggle('hidden');
});

// Khi đăng nhập thành công sẽ thay đổi navbar
const userDropdownToggle = document.getElementById('user-dropdown-toggle');
if (userDropdownToggle) {
    userDropdownToggle.addEventListener('click', function () {
        const userDropdown = document.getElementById('user-dropdown');
        if (userDropdown) {
            userDropdown.classList.toggle('hidden');
        }
    });
}

// Mobile User Dropdown Toggle
const mobileUserDropdownToggle = document.getElementById('mobile-user-dropdown-toggle');
if (mobileUserDropdownToggle) {
    mobileUserDropdownToggle.addEventListener('click', function () {
        const mobileUserDropdown = document.getElementById('mobile-user-dropdown');
        if (mobileUserDropdown) {
            mobileUserDropdown.classList.toggle('hidden');
        }
    });
}


// Xử lý việc thêm SP vào giỏ với Ajax (không tải lại trang)
document.addEventListener("DOMContentLoaded", () => {
    const addToCartButtons = document.querySelectorAll('.add-to-cart-button');

    const handleAddToCart = async (event) => {
        event.preventDefault();
        const form = event.target.closest('.add-to-cart-form');
        const formData = new FormData(form);
        formData.append('action', 'add_to_cart');

        try {
            const response = await fetch('/ajax.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) {
                throw new Error('Response is not JSON or status is not OK');
            }

            const data = await response.json();
            handleResponse(data);
        } catch (error) {
            console.error('Fetch Error:', error);
            // alert("An error occurred. Please try again.");
        }
    };

    const handleResponse = (data) => {
        const messageContainer = document.createElement('div');
        messageContainer.classList.add('fixed', 'top-4', 'right-4', 'z-50', 'p-4', 'rounded-lg', 'shadow-lg', 'transition-all', 'duration-300');

        // Kiểm tra nếu đăng nhập chưa, hoặc thông báo thành công/lỗi
        if (data.loggedIn === false) {
            showLoginModal();
        } else if (data.success) {
            // Thông báo thành công
            messageContainer.classList.add('bg-green-100', 'border', 'border-green-400', 'text-green-700');
            messageContainer.innerHTML = `
            <span>Product added to cart successfully!</span>
            <button onclick="this.parentElement.remove()" class="ml-2 text-sm font-semibold">✕</button>
        `;
        } else {
            // Thông báo lỗi
            messageContainer.classList.add('bg-red-100', 'border', 'border-red-400', 'text-red-700');
            messageContainer.innerHTML = `
            <span>Cannot add to cart! The product is out of stock.</span>
            <button onclick="this.parentElement.remove()" class="ml-2 text-sm font-semibold">✕</button>
        `;
        }

        // Thêm thông báo vào body
        document.body.appendChild(messageContainer);

        setTimeout(() => location.reload(), 500);
    };

    addToCartButtons.forEach(button => {
        button.addEventListener('click', handleAddToCart);
    });
});

function showLoginModal() {
    const modalHtml = `
        <div id="loginModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50" >
            <div class="bg-white shadow-2xl rounded-xl max-w-xl w-full p-12 text-center transform scale-95 transition-transform duration-300">
                <h2 class="text-2xl font-bold mb-6 text-gray-900">Please LOGIN to add items to your cart!</h2>
                <p class="mb-8 text-lg text-gray-600">To add products to your cart, you need to log in. You can also continue browsing product details.</p>
                <div class="flex justify-center">
                    <button type="button" class="bg-blue-500 text-white font-semibold px-6 py-2 rounded-xl shadow-lg hover:bg-blue-600 hover:shadow-lg transition duration-300 ease-in-out transform hover:scale-105 w-full sm:w-3/5"
                        onclick="window.location.href='/login'">Login</button>
                </div>
            </div>
        </div>  `;

    // Xóa modal cũ nếu có
    const existingModal = document.getElementById('loginModal');
    if (existingModal) {
        existingModal.remove();
    }

    document.body.insertAdjacentHTML('beforeend', modalHtml);

    document.getElementById('continueBrowsing').addEventListener('click', function () {
        document.getElementById('loginModal').remove();
    });

    document.getElementById('loginModal').addEventListener('click', function (event) {
        if (event.target === this) {
            this.remove();
        }
    });
}