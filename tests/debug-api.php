<?php
// Debug API calls for Festival System
require_once '../config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>🎪 Debug API</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #fff; }
        .test-section { margin: 20px 0; padding: 20px; border: 1px solid #333; border-radius: 5px; }
        .success { color: #4ade80; }
        .error { color: #f87171; }
        button { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; margin: 5px; }
        button:hover { background: #2563eb; }
        pre { background: #2d2d2d; padding: 10px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🎪 Debug API Calls</h1>

    <div class="test-section">
        <h3>Test Database Connection</h3>
        <button onclick="testDB()">Test DB</button>
        <div id="db-result"></div>
    </div>

    <div class="test-section">
        <h3>Test Validate API</h3>
        <input type="text" id="test-userid" placeholder="Enter User ID" value="123456789">
        <button onclick="testValidate()">Test Validate</button>
        <div id="validate-result"></div>
    </div>

    <div class="test-section">
        <h3>Test Admin API (Themes)</h3>
        <button onclick="testAdminThemes()">Test Admin Themes</button>
        <div id="admin-themes-result"></div>
    </div>

    <div class="test-section">
        <h3>Test Participate API</h3>
        <button onclick="testParticipate()">Test Participate</button>
        <div id="participate-result"></div>
    </div>

    <script>
        function testDB() {
            console.log('Testing database connection...');
            document.getElementById('db-result').innerHTML = '<pre>در حال بارگذاری...</pre>';

            // Simple test
            fetch('../utils/status.php', {
                method: 'GET',
                headers: {
                    'Accept': 'text/html'
                }
            })
            .then(r => {
                console.log('Response status:', r.status, 'OK:', r.ok);
                if (!r.ok) {
                    throw new Error(`HTTP ${r.status}: ${r.statusText}`);
                }
                return r.text();
            })
            .then(html => {
                console.log('Received HTML, length:', html.length);
                // Extract just the status content - find the body content
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const body = doc.querySelector('body');
                if (body) {
                    // Remove the title and footer
                    const title = body.querySelector('h1');
                    const footer = Array.from(body.querySelectorAll('*')).find(el =>
                        el.textContent && el.textContent.includes('بررسی شده در')
                    );
                    if (title) title.remove();
                    if (footer && footer.parentNode) footer.parentNode.remove();

                    document.getElementById('db-result').innerHTML = '<pre>' + body.innerHTML + '</pre>';
                } else {
                    document.getElementById('db-result').innerHTML = '<pre>دریافت موفق - اما پردازش ناموفق</pre>';
                }
            })
            .catch(e => {
                console.error('DB test error:', e);
                document.getElementById('db-result').innerHTML = '<pre class="error">Error: ' + e.message + '</pre>';
            });
        }

        function testValidate() {
            const userId = document.getElementById('test-userid').value;
            fetch('api/validate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ telegram_id: userId })
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('validate-result').innerHTML = '<pre class="' + (data.success ? 'success' : 'error') + '">' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(e => {
                document.getElementById('validate-result').innerHTML = '<pre class="error">Error: ' + e.message + '</pre>';
            });
        }

        function testAdminThemes() {
            fetch('api/admin-api.php?action=get_themes')
                .then(r => r.json())
                .then(data => {
                    document.getElementById('admin-themes-result').innerHTML = '<pre class="' + (data.success ? 'success' : 'error') + '">' + JSON.stringify(data, null, 2) + '</pre>';
                })
                .catch(e => {
                    document.getElementById('admin-themes-result').innerHTML = '<pre class="error">Error: ' + e.message + '</pre>';
                });
        }

        function testParticipate() {
            fetch('api/participate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: '123456789' })
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('participate-result').innerHTML = '<pre class="' + (data.status === 'ok' ? 'success' : 'error') + '">' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(e => {
                document.getElementById('participate-result').innerHTML = '<pre class="error">Error: ' + e.message + '</pre>';
            });
        }

        // Auto-run DB test on load
        testDB();
    </script>
</body>
</html>
