<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Test Admin API</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #fff; }
        .result { margin: 20px 0; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        pre { background: #2d2d2d; padding: 10px; border-radius: 5px; overflow-x: auto; }
        button { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #2563eb; }
    </style>
</head>
<body>
    <h1>🧪 Test Admin API</h1>

    <h3>Test Dashboard Stats</h3>
    <button onclick="testDashboard()">Test Dashboard</button>
    <div id="dashboard-result" class="result"></div>

    <h3>Test Themes</h3>
    <button onclick="testThemes()">Test Themes</button>
    <div id="themes-result" class="result"></div>

    <script>
        async function testDashboard() {
            console.log('Testing dashboard...');
            document.getElementById('dashboard-result').innerHTML = '<pre>در حال بارگذاری...</pre>';

            try {
                const response = await fetch('api/admin-api.php?action=dashboard_stats&range=24h', {
                    credentials: 'include'
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                const data = await response.json();
                console.log('Dashboard data:', data);

                document.getElementById('dashboard-result').innerHTML =
                    '<pre class="' + (data.success ? 'success' : 'error') + '">' +
                    JSON.stringify(data, null, 2) + '</pre>';

            } catch (e) {
                console.error('Dashboard error:', e);
                document.getElementById('dashboard-result').innerHTML =
                    '<pre class="error">Error: ' + e.message + '</pre>';
            }
        }

        async function testThemes() {
            console.log('Testing themes...');
            document.getElementById('themes-result').innerHTML = '<pre>در حال بارگذاری...</pre>';

            try {
                const response = await fetch('api/admin-api.php?action=get_themes', {
                    credentials: 'include'
                });

                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Themes data:', data);

                document.getElementById('themes-result').innerHTML =
                    '<pre class="' + (data.success ? 'success' : 'error') + '">' +
                    JSON.stringify(data, null, 2) + '</pre>';

            } catch (e) {
                console.error('Themes error:', e);
                document.getElementById('themes-result').innerHTML =
                    '<pre class="error">Error: ' + e.message + '</pre>';
            }
        }
    </script>
</body>
</html>
