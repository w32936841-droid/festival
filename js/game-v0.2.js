// version: 0.2 - Game Logic

// ========== متغیرهای سراسری ==========
let userId = '';
let giftCount = 0;
let gameActive = false;
let fruitSpawnInterval;
let preGameSpawnInterval;

// عناصر DOM
const welcomeScreen = document.getElementById('welcomeScreen');
const gameScreen = document.getElementById('gameScreen');
const guideModal = document.getElementById('guideModal');
const giftModal = document.getElementById('giftModal');
const loading = document.getElementById('loading');

const userIdInput = document.getElementById('userId');
const startBtn = document.getElementById('startBtn');
const startGameBtn = document.getElementById('startGameBtn');
const continueBtn = document.getElementById('continueBtn');

const gameArea = document.getElementById('gameArea');
const displayUserId = document.getElementById('displayUserId');
const giftCountDisplay = document.getElementById('giftCount');
const particleContainer = document.getElementById('particleContainer');

const giftTitle = document.getElementById('giftTitle');
const giftCode = document.getElementById('giftCode');

// تنظیمات بازی
const FRUIT_TYPES = {
    pomegranate: {
        emoji: '🍎',
        color: '#c62828',
        particle: '🔴'
    },
    watermelon: {
        emoji: '🍉',
        color: '#e91e63',
        particle: '⚪'
    }
};

const PRE_GAME_SPAWN_RATE = 2000; // هر 2 ثانیه (حالت نمایشی)
const GAME_SPAWN_RATE = 1000;     // هر 1 ثانیه (حین بازی - 50% سریع‌تر)

// ========== Event Listeners ==========
startBtn.addEventListener('click', showGuide);
startGameBtn.addEventListener('click', startGame);
continueBtn.addEventListener('click', closeGiftModal);
giftCode.addEventListener('click', copyGiftCode);

// ========== توابع اصلی ==========

// نمایش راهنما
function showGuide() {
    userId = userIdInput.value.trim();
    
    if (!userId || !/^\d+$/.test(userId)) {
        alert('لطفاً یک آیدی عددی معتبر وارد کنید');
        return;
    }
    
    guideModal.classList.add('active');
    
    // شروع ریزش میوه‌های نمایشی (غیرقابل کلیک)
    startPreGameFruits();
}

// شروع بازی
function startGame() {
    guideModal.classList.remove('active');
    welcomeScreen.classList.remove('active');
    gameScreen.classList.add('active');
    
    displayUserId.textContent = userId;
    gameActive = true;
    
    // متوقف کردن میوه‌های نمایشی
    if (preGameSpawnInterval) {
        clearInterval(preGameSpawnInterval);
    }
    
    // پاک کردن میوه‌های قبلی
    gameArea.innerHTML = '';
    
    // شروع اسپاون میوه‌های بازی (با شدت بیشتر)
    startGameFruits();
}

// ریزش میوه‌های نمایشی (قبل از شروع بازی)
function startPreGameFruits() {
    preGameSpawnInterval = setInterval(() => {
        spawnFruit(false); // غیرقابل کلیک
    }, PRE_GAME_SPAWN_RATE);
}

// ریزش میوه‌های بازی (قابل کلیک)
function startGameFruits() {
    fruitSpawnInterval = setInterval(() => {
        spawnFruit(true); // قابل کلیک
    }, GAME_SPAWN_RATE);
}

// ساخت میوه
function spawnFruit(clickable = true) {
    const fruit = document.createElement('div');
    fruit.className = 'fruit';
    
    // انتخاب تصادفی نوع میوه
    const fruitType = Math.random() > 0.5 ? 'pomegranate' : 'watermelon';
    const fruitData = FRUIT_TYPES[fruitType];
    
    fruit.textContent = fruitData.emoji;
    fruit.style.fontSize = '50px';
    fruit.dataset.type = fruitType;
    
    // موقعیت تصادفی افقی
    const leftPos = Math.random() * (window.innerWidth - 60);
    fruit.style.left = leftPos + 'px';
    fruit.style.top = '-60px';
    
    gameArea.appendChild(fruit);
    
    // انیمیشن سقوط
    animateFruit(fruit, clickable);
}

// انیمیشن سقوط میوه
function animateFruit(fruit, clickable) {
    const duration = 5000 + Math.random() * 2000; // 5-7 ثانیه
    const startTime = Date.now();
    const startTop = -60;
    const endTop = window.innerHeight;
    
    function animate() {
        const elapsed = Date.now() - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const currentTop = startTop + (endTop - startTop) * progress;
        fruit.style.top = currentTop + 'px';
        
        if (progress < 1 && gameArea.contains(fruit)) {
            requestAnimationFrame(animate);
        } else {
            // میوه از صفحه خارج شد
            if (fruit.parentNode) {
                fruit.remove();
            }
        }
    }
    
    animate();
    
    // اگر قابل کلیک باشه
    if (clickable) {
        fruit.addEventListener('click', () => onFruitClick(fruit));
    }
}

// کلیک روی میوه
async function onFruitClick(fruit) {
    if (!gameActive) return;
    
    const fruitType = fruit.dataset.type;
    const fruitData = FRUIT_TYPES[fruitType];
    const rect = fruit.getBoundingClientRect();
    
    // انیمیشن انفجار
    fruit.classList.add('explode');
    
    // ساخت ذرات (دانه‌ها)
    createParticles(rect.left + rect.width / 2, rect.top + rect.height / 2, fruitData.particle);
    
    // حذف میوه بعد از انیمیشن
    setTimeout(() => {
        if (fruit.parentNode) {
            fruit.remove();
        }
    }, 600);
    
    // فراخوانی API برای دریافت هدیه
    await fetchGift();
}

// ساخت ذرات انفجار
function createParticles(x, y, emoji) {
    const particleCount = 15;
    
    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.textContent = emoji;
        particle.style.fontSize = '20px';
        particle.style.left = x + 'px';
        particle.style.top = y + 'px';
        
        const angle = (Math.PI * 2 * i) / particleCount;
        const velocity = 100 + Math.random() * 100;
        const vx = Math.cos(angle) * velocity;
        const vy = Math.sin(angle) * velocity;
        
        particle.style.setProperty('--x', vx + 'px');
        particle.style.setProperty('--y', vy + 'px');
        
        particleContainer.appendChild(particle);
        
        setTimeout(() => {
            particle.remove();
        }, 1000);
    }
}

// دریافت هدیه از API
async function fetchGift() {
    loading.classList.remove('hidden');
    
    try {
        const formData = new FormData();
        formData.append('user_id', userId);
        
        const response = await fetch('api/get-gift.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        loading.classList.add('hidden');
        
        if (data.success) {
            showGiftModal(data.gift);
            giftCount++;
            giftCountDisplay.textContent = giftCount;
        } else {
            alert('خطا: ' + data.message);
        }
    } catch (error) {
        loading.classList.add('hidden');
        alert('خطا در ارتباط با سرور');
        console.error(error);
    }
}

// نمایش مودال هدیه
function showGiftModal(gift) {
    giftTitle.textContent = gift.title;
    giftCode.textContent = gift.code;
    giftModal.classList.add('active');
    
    // متوقف کردن بازی موقتاً
    gameActive = false;
}

// بستن مودال هدیه و ادامه بازی
function closeGiftModal() {
    giftModal.classList.remove('active');
    gameActive = true;
}

// کپی کردن کد هدیه
function copyGiftCode() {
    const code = giftCode.textContent;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(code).then(() => {
            // تغییر موقت متن برای نشان دادن کپی شدن
            const originalText = giftCode.textContent;
            giftCode.textContent = '✅ کپی شد!';
            setTimeout(() => {
                giftCode.textContent = originalText;
            }, 1500);
        });
    } else {
        // روش قدیمی برای مرورگرهای قدیمی
        const textArea = document.createElement('textarea');
        textArea.value = code;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        textArea.remove();
        
        const originalText = giftCode.textContent;
        giftCode.textContent = '✅ کپی شد!';
        setTimeout(() => {
            giftCode.textContent = originalText;
        }, 1500);
    }
}

// ========== شروع اولیه ==========
document.addEventListener('DOMContentLoaded', () => {
    console.log('🎁 جشنواره شب یلدا - نسخه 0.2');
});
