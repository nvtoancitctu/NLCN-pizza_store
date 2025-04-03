<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy">
    <title>Lover's Hut</title>
    <link rel="icon" type="image/png" href="/images/logo.png">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Lobster&family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Font Awesome (cho biểu tượng) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css?v=3.0">

    <!-- Inline CSS cho hình nền và font chữ -->
    <style>
        body {
            background-size: cover;
            font-family: 'Poppins', sans-serif;
        }

        #back-to-top {
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        #back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col font-medium bg-gray-50">
    <!-- Nút quay về đầu trang -->
    <button id="back-to-top" class="fixed bottom-24 right-4 p-3 bg-pink-500 text-white rounded-full shadow-lg hover:bg-pink-600 transition-all duration-300 opacity-0 invisible">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    <!-- Nút đi tới cuối trang -->
    <button id="go-to-bottom" class="fixed bottom-8 right-4 p-3 bg-blue-500 text-white rounded-full shadow-lg hover:bg-blue-600 transition-all duration-300 opacity-0 invisible">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
        </svg>
    </button>

    <!-- JavaScript -->
    <script>
        const backToTopButton = document.getElementById('back-to-top');
        const goToBottomButton = document.getElementById('go-to-bottom');

        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopButton.classList.add('visible', 'opacity-100');
                backToTopButton.classList.remove('invisible', 'opacity-0');
            } else {
                backToTopButton.classList.remove('visible', 'opacity-100');
                backToTopButton.classList.add('invisible', 'opacity-0');
            }

            if (window.innerHeight + window.scrollY < document.body.offsetHeight - 300) {
                goToBottomButton.classList.add('visible', 'opacity-100');
                goToBottomButton.classList.remove('invisible', 'opacity-0');
            } else {
                goToBottomButton.classList.remove('visible', 'opacity-100');
                goToBottomButton.classList.add('invisible', 'opacity-0');
            }
        });

        backToTopButton.addEventListener('click', () => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        goToBottomButton.addEventListener('click', () => {
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'smooth'
            });
        });
    </script>

</body>

</html>