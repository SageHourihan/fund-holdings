<?php include_once 'views/header.html' ?>
<?php include_once 'views/navbar.html' ?>
<body class="bg-gray-100 min-h-screen">
    <div id="loader" class="fixed inset-0 bg-white bg-opacity-80 z-50 flex items-center justify-center" style="display:none;">
        <div class="w-12 h-12 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Modified container to allow full width -->
    <div class="container-fluid px-4 py-8 mx-auto">
        <div class="max-w-full mx-auto space-y-4">
            <div class="space-y-4 mb-6">
                <input id="funds" type="text" placeholder="Enter fund tickers (comma-separated)" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

                <input id="investments" type="text" placeholder="Corresponding investments (comma-separated)" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">

                <div class="flex space-x-4">
                    <button id="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Submit
                    </button>

                    <button id="export-excel" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        Export to Excel
                    </button>
                </div>
            </div>

            <!-- Make the body div full-width -->
            <div id="body" class="w-full overflow-x-auto"></div>

            <button id="top" class="fixed bottom-6 right-6 p-3 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>
        </div>
    </div>
</body>
</html>
