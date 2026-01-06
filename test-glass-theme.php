<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>Test Glass Theme</title>
    <style>
        :root {
            --primary-color: rgba(255,255,255,0.3);
            --secondary-color: rgba(255,255,255,0.1);
        }

        body {
            font-family: 'Vazir', sans-serif;
            background: linear-gradient(135deg, #0b1020 0%, #1a1a2e 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            color: white;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 24px;
            padding: 40px 30px;
            max-width: 500px;
            margin: 50px auto;
            box-shadow:
                0 8px 32px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--primary-color), var(--secondary-color));
            border-radius: 26px;
            z-index: -1;
            animation: rotateBorder 4s linear infinite;
            background-size: 200% 200%;
        }

        @keyframes rotateBorder {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .title {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 10px;
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
        }

        .subtitle {
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 25px;
            color: rgba(255, 255, 255, 0.9);
        }

        .btn {
            width: 100%;
            padding: 18px 24px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: #ffffff;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div class="glass-card">
        <h1 class="title">
            <span>شب یلدا مبارک</span>
        </h1>
        <h2 class="subtitle">
            <span>جشن شب چله</span>
        </h2>

        <p style="text-align: center; color: rgba(255,255,255,0.9); margin-bottom: 30px;">
            آیا پنجره شیشه‌ای و بی‌رنگ به نظر می‌رسد؟
        </p>

        <button class="btn">ورود به جشنواره</button>
    </div>

    <div style="text-align: center; margin-top: 50px; color: #94a3b8;">
        <p>🎨 رنگ‌های شیشه‌ای: شفاف/سفید کم‌رنگ</p>
        <p>✨ جلوه: backdrop-filter blur + transparency</p>
    </div>
</body>
</html>
