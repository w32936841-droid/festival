// ============================================
// YALDA PROJECT - MAIN JAVASCRIPT
// Handles ID validation, fruit animation, API calls, language toggle
// ============================================

// ============================================
// 1. CONFIGURATION & GLOBAL VARIABLES
// ============================================

const CONFIG = {
    // Fruit falling animation settings
    fruitSpawnInterval: 800,
    fruitSpawnIntervalGame: 500, // Faster during game
    fruitFallDuration: [8000, 12000],
    maxFruitsOnScreen: 15,
    maxFruitsOnScreenGame: 25, // More fruits during game
    fruitSize: 60,
    
    // Seed burst settings
    seedCount: 30,
    seedBurstRadius: 180,
    
    // API endpoint
    apiValidateUrl: 'api/validate.php',
    apiGiftUrl: 'api/gift.php',
    
    // Fruit types
    fruits: [
        { name: 'watermelon', image: 'assets/watermelon.png', weight: 0.5 },
        { name: 'pomegranate', image: 'assets/pomegranate.png', weight: 0.5 }
    ]
};

// Global state management
const STATE = {
    userId: null,
    isValidated: false,
    fruitsArray: [],
    fruitSpawnTimer: null,
    canClickFruit: false,
    currentLanguage: 'fa',
    gameStarted: false
};

// ============================================
// 2. DOM ELEMENTS REFERENCES
// ============================================

const DOM = {
    // Language toggle
    langToggle: document.getElementById('lang-toggle'),
    htmlElement: document.documentElement,
    
    // Logo
    logoContainer: document.getElementById('logo-container'),
    
    // Input section
    telegramInput: document.getElementById('telegram-id'),
    confirmBtn: document.getElementById('confirm-btn'),
    validationMessage: document.getElementById('validation-message'),
    pasteBtn: document.getElementById('paste-btn'),
    
    // Help section
    helpBtn: document.getElementById('help-btn'),
    helpContent: document.getElementById('help-content'),
    
    // Sections
    idInputSection: document.getElementById('id-input-section'),
    startGameSection: document.getElementById('start-game-section'),
    catchFruitSection: document.getElementById('catch-fruit-section'),
    giftSection: document.getElementById('gift-section'),
    
    // Containers
    fruitsContainer: document.getElementById('fruits-container'),
    seedsContainer: document.getElementById('seeds-container'),
    
    // Game buttons
    startGameBtn: document.getElementById('start-game-btn'),
    
    // Loading
    loadingIndicator: document.getElementById('loading-indicator'),
    
    // Gift display
    giftCode: document.getElementById('gift-code'),
    giftDescription: document.getElementById('gift-description'),
    copyBtn: document.getElementById('copy-btn')
};

// ============================================
// 3. INITIALIZATION
// ============================================

function init() {
    console.log('🎉 Yalda Project Initialized');
    
    // Detect browser language
    detectAndSetLanguage();
    
    // Event listeners
    DOM.langToggle.addEventListener('click', toggleLanguage);
    DOM.telegramInput.addEventListener('input', handleInputChange);
    DOM.telegramInput.addEventListener('keypress', handleEnterKey);
    DOM.confirmBtn.addEventListener('click', handleConfirmClick);
    DOM.pasteBtn.addEventListener('click', handlePasteClick);
    DOM.helpBtn.addEventListener('click', toggleHelp);
    DOM.startGameBtn.addEventListener('click', handleStartGame);
    DOM.copyBtn.addEventListener('click', handleCopyCode);
    
    // Make copyable elements work
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('copyable')) {
            copyToClipboard(e.target.textContent.trim());
            
            // Visual feedback
            const original = e.target.style.background;
            e.target.style.background = 'rgba(74, 222, 128, 0.3)';
            setTimeout(() => {
                e.target.style.background = original;
            }, 500);
        }
    });
    
    // Start background fruit animation
    startBackgroundFruitAnimation();
}

// ============================================
// 4. LANGUAGE TOGGLE SYSTEM
// ============================================

function detectAndSetLanguage() {
    const browserLang = navigator.language || navigator.userLanguage;
    
    if (browserLang.startsWith('fa') || browserLang.startsWith('ar')) {
        STATE.currentLanguage = 'fa';
    } else {
        STATE.currentLanguage = 'en';
    }
    
    applyLanguage(STATE.currentLanguage);
}

function toggleLanguage() {
    STATE.currentLanguage = STATE.currentLanguage === 'fa' ? 'en' : 'fa';
    applyLanguage(STATE.currentLanguage);
}

function applyLanguage(lang) {
    console.log('🌐 Language switched to:', lang);
    
    DOM.htmlElement.setAttribute('data-lang', lang);
    
    if (lang === 'fa') {
        DOM.htmlElement.setAttribute('dir', 'rtl');
    } else {
        DOM.htmlElement.setAttribute('dir', 'ltr');
    }
}

// ============================================
// 5. INPUT VALIDATION
// ============================================

function handleInputChange(e) {
    const value = e.target.value.trim();
    
    DOM.validationMessage.textContent = '';
    DOM.validationMessage.className = 'validation-message';
    
    if (value === '') {
        DOM.confirmBtn.disabled = true;
        return;
    }
    
    if (!/^\d+$/.test(value)) {
        showValidationMessage(
            STATE.currentLanguage === 'fa' ? 'فقط اعداد مجاز است' : 'Only numbers allowed',
            'error'
        );
        DOM.confirmBtn.disabled = true;
        return;
    }
    
    if (value.length < 5) {
        showValidationMessage(
            STATE.currentLanguage === 'fa' ? 'آیدی کاربری باید حداقل ۵ رقم باشد' : 'Minimum 5 digits',
            'error'
        );
        DOM.confirmBtn.disabled = true;
        return;
    }
    
    if (value.length > 15) {
        showValidationMessage(
            STATE.currentLanguage === 'fa' ? 'آیدی کاربری معتبر نیست' : 'Invalid User ID',
            'error'
        );
        DOM.confirmBtn.disabled = true;
        return;
    }
    
    showValidationMessage(
        STATE.currentLanguage === 'fa' ? '✓ آیدی معتبر است' : '✓ Valid ID',
        'success'
    );
    DOM.confirmBtn.disabled = false;
    STATE.userId = value;
}

function showValidationMessage(message, type) {
    DOM.validationMessage.textContent = message;
    DOM.validationMessage.className = `validation-message ${type}`;
}

function handleEnterKey(e) {
    if (e.key === 'Enter' && !DOM.confirmBtn.disabled) {
        handleConfirmClick();
    }
}

// ============================================
// 6. PASTE BUTTON FUNCTIONALITY
// ============================================

async function handlePasteClick() {
    try {
        const text = await navigator.clipboard.readText();
        DOM.telegramInput.value = text.trim();
        DOM.telegramInput.dispatchEvent(new Event('input'));
        console.log('📋 Pasted:', text);
    } catch (error) {
        console.error('❌ Paste failed:', error);
        alert(STATE.currentLanguage === 'fa' ? 
            'لطفاً دسترسی کپی کردن را مجاز کنید' : 
            'Please allow clipboard access');
    }
}

// ============================================
// 7. HELP SECTION TOGGLE
// ============================================

function toggleHelp() {
    DOM.helpContent.classList.toggle('show');
}

function copyToClipboard(text) {
    try {
        navigator.clipboard.writeText(text);
        console.log('📋 Copied:', text);
    } catch (error) {
        console.error('❌ Copy failed:', error);
    }
}

// ============================================
// 8. CONFIRM BUTTON - API VALIDATION
// ============================================

async function handleConfirmClick() {
    if (!STATE.userId) return;
    
    console.log('📞 Validating user ID:', STATE.userId);
    
    DOM.confirmBtn.disabled = true;
    const originalHTML = DOM.confirmBtn.innerHTML;
    DOM.confirmBtn.innerHTML = STATE.currentLanguage === 'fa' ? 
        '<span data-lang="fa">در حال بررسی...</span>' : 
        '<span data-lang="en">Validating...</span>';
    
    try {
        const response = await fetch(CONFIG.apiValidateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                telegram_id: STATE.userId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('✅ User validated');
            STATE.isValidated = true;
            transitionToStartGameSection();
        } else {
            console.log('❌ Validation failed:', data.message);
            showValidationMessage(
                data.message || (STATE.currentLanguage === 'fa' ? 'آیدی یافت نشد' : 'ID not found'),
                'error'
            );
            DOM.confirmBtn.disabled = false;
            DOM.confirmBtn.innerHTML = originalHTML;
        }
        
    } catch (error) {
        console.error('❌ API Error:', error);
        console.log('⚠️ TEST MODE: Continuing without validation');
        STATE.isValidated = true;
        transitionToStartGameSection();
    }
}

// ============================================
// 9. SECTION TRANSITIONS
// ============================================

function transitionToStartGameSection() {
    console.log('🎮 Transitioning to start game section');
    
    DOM.idInputSection.classList.remove('active');
    
    setTimeout(() => {
        DOM.startGameSection.classList.add('active');
    }, 300);
}

function handleStartGame() {
    console.log('🎯 Starting game');
    
    STATE.gameStarted = true;
    
    // Hide logo
    DOM.logoContainer.classList.add('hidden');
    
    DOM.startGameSection.classList.remove('active');
    
    // Stop current fruit animation
    if (STATE.fruitSpawnTimer) {
        clearInterval(STATE.fruitSpawnTimer);
    }
    
    // Start game mode with more fruits
    startGameFruitAnimation();
    
    setTimeout(() => {
        DOM.catchFruitSection.classList.add('active');
        STATE.canClickFruit = true;
        
        // Make all fruits clickable
        STATE.fruitsArray.forEach(fruit => {
            fruit.element.style.pointerEvents = 'auto';
        });
    }, 300);
}

function transitionToGiftSection(giftData) {
    console.log('🎁 Transitioning to gift section');
    
    DOM.catchFruitSection.classList.remove('active');
    stopFruitAnimation();
    
    setTimeout(() => {
        DOM.giftSection.classList.add('active');
        DOM.giftCode.textContent = giftData.code || 'ERROR';
        
        if (giftData.description) {
            DOM.giftDescription.textContent = giftData.description;
        }
    }, 300);
}

// ============================================
// 10. FRUIT ANIMATION SYSTEM
// ============================================

function startBackgroundFruitAnimation() {
    console.log('🍉 Starting background fruit animation');
    
    for (let i = 0; i < 5; i++) {
        setTimeout(() => createFallingFruit(false), i * 500);
    }
    
    STATE.fruitSpawnTimer = setInterval(() => {
        const maxFruits = STATE.gameStarted ? CONFIG.maxFruitsOnScreenGame : CONFIG.maxFruitsOnScreen;
        if (STATE.fruitsArray.length < maxFruits) {
            createFallingFruit(STATE.gameStarted);
        }
    }, CONFIG.fruitSpawnInterval);
}

function startGameFruitAnimation() {
    console.log('🎮 Starting game fruit animation');
    
    // Create more initial fruits
    for (let i = 0; i < 10; i++) {
        setTimeout(() => createFallingFruit(true), i * 200);
    }
    
    STATE.fruitSpawnTimer = setInterval(() => {
        if (STATE.fruitsArray.length < CONFIG.maxFruitsOnScreenGame) {
            createFallingFruit(true);
        }
    }, CONFIG.fruitSpawnIntervalGame);
}

function createFallingFruit(isGameMode) {
    const fruitType = selectRandomFruit();
    
    const fruitDiv = document.createElement('div');
    fruitDiv.className = 'falling-fruit';
    
    const fruitImg = document.createElement('img');
    fruitImg.src = fruitType.image;
    fruitImg.alt = fruitType.name;
    fruitDiv.appendChild(fruitImg);
    
    const randomX = Math.random() * (window.innerWidth - CONFIG.fruitSize);
    fruitDiv.style.left = randomX + 'px';
    
    const duration = CONFIG.fruitFallDuration[0] + 
                     Math.random() * (CONFIG.fruitFallDuration[1] - CONFIG.fruitFallDuration[0]);
    fruitDiv.style.animationDuration = duration + 'ms';
    
    // Fruits are clickable only during game mode
    fruitDiv.style.pointerEvents = (isGameMode && STATE.canClickFruit) ? 'auto' : 'none';
    
    fruitDiv.addEventListener('click', (e) => handleFruitClick(fruitType, fruitDiv, e));
    
    DOM.fruitsContainer.appendChild(fruitDiv);
    
    const fruitObject = {
        element: fruitDiv,
        type: fruitType.name
    };
    STATE.fruitsArray.push(fruitObject);
    
    setTimeout(() => {
        removeFruit(fruitDiv);
    }, duration);
}

function selectRandomFruit() {
    const random = Math.random();
    let cumulativeWeight = 0;
    
    for (const fruit of CONFIG.fruits) {
        cumulativeWeight += fruit.weight;
        if (random <= cumulativeWeight) {
            return fruit;
        }
    }
    
    return CONFIG.fruits[0];
}

function removeFruit(fruitElement) {
    if (fruitElement && fruitElement.parentNode) {
        fruitElement.parentNode.removeChild(fruitElement);
    }
    
    STATE.fruitsArray = STATE.fruitsArray.filter(f => f.element !== fruitElement);
}

function stopFruitAnimation() {
    console.log('🛑 Stopping fruit animation');
    
    if (STATE.fruitSpawnTimer) {
        clearInterval(STATE.fruitSpawnTimer);
        STATE.fruitSpawnTimer = null;
    }
    
    STATE.fruitsArray.forEach(fruit => {
        fruit.element.style.transition = 'opacity 0.5s';
        fruit.element.style.opacity = '0';
        setTimeout(() => removeFruit(fruit.element), 500);
    });
}

// ============================================
// 11. SEED BURST ANIMATION
// ============================================

function createSeedBurst(x, y, fruitType) {
    console.log('💥 Seed burst at:', x, y, 'Type:', fruitType);
    
    const seedClass = fruitType === 'watermelon' ? 'seed-watermelon' : 'seed-pomegranate';
    
    for (let i = 0; i < CONFIG.seedCount; i++) {
        const seed = document.createElement('div');
        seed.className = `seed-particle ${seedClass}`;
        
        const angle = (Math.PI * 2 * i) / CONFIG.seedCount + (Math.random() * 0.4);
        const distance = CONFIG.seedBurstRadius * (0.5 + Math.random() * 0.5);
        
        const tx = Math.cos(angle) * distance;
        const ty = Math.sin(angle) * distance;
        
        seed.style.left = x + 'px';
        seed.style.top = y + 'px';
        seed.style.setProperty('--tx', tx + 'px');
        seed.style.setProperty('--ty', ty + 'px');
        seed.style.animationDelay = (Math.random() * 0.1) + 's';
        
        DOM.seedsContainer.appendChild(seed);
        
        setTimeout(() => {
            if (seed.parentNode) {
                seed.parentNode.removeChild(seed);
            }
        }, 1400);
    }
}

// ============================================
// 12. FRUIT CLICK HANDLER - GET GIFT
// ============================================

async function handleFruitClick(fruitType, fruitElement, event) {
    if (!STATE.canClickFruit || !STATE.gameStarted) return;
    
    console.log('🎯 Fruit clicked:', fruitType.name);
    
    STATE.canClickFruit = false;
    
    // Get click position
    const rect = fruitElement.getBoundingClientRect();
    const x = rect.left + rect.width / 2;
    const y = rect.top + rect.height / 2;
    
    // Create seed burst animation
    createSeedBurst(x, y, fruitType.name);
    
    // Visual feedback
    fruitElement.style.transform = 'scale(1.5)';
    fruitElement.style.opacity = '0';
    setTimeout(() => removeFruit(fruitElement), 300);
    
    // Show loading
    DOM.loadingIndicator.style.display = 'block';
    
    try {
        const response = await fetch(CONFIG.apiGiftUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                telegram_id: STATE.userId,
                fruit_type: fruitType.name
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Gift received:', data);
            
            setTimeout(() => {
                DOM.loadingIndicator.style.display = 'none';
                transitionToGiftSection(data.gift);
            }, 1000);
        } else {
            console.log('❌ Gift error:', data.message);
            alert(data.message || (STATE.currentLanguage === 'fa' ? 
                'خطا در دریافت هدیه' : 'Error receiving gift'));
            DOM.loadingIndicator.style.display = 'none';
            STATE.canClickFruit = true;
        }
    } catch (error) {
        console.error('❌ API Error:', error);
        console.log('⚠️ TEST MODE: Showing test gift');
        
        setTimeout(() => {
            DOM.loadingIndicator.style.display = 'none';
            transitionToGiftSection({
                code: 'YALDA2025-TEST',
                description: STATE.currentLanguage === 'fa' ? 
                    '🎁 کد تست - تخفیف ۲۰٪' : 
                    '🎁 Test Code - 20% Discount'
            });
        }, 1500);
    }
}

// ============================================
// 13. GIFT CODE COPY FUNCTIONALITY
// ============================================

async function handleCopyCode() {
    const code = DOM.giftCode.textContent;
    
    try {
        await navigator.clipboard.writeText(code);
        
        const originalHTML = DOM.copyBtn.innerHTML;
        DOM.copyBtn.innerHTML = '✓';
        DOM.copyBtn.style.background = 'rgba(74, 222, 128, 0.3)';
        
        console.log('📋 Code copied:', code);
        
        setTimeout(() => {
            DOM.copyBtn.innerHTML = originalHTML;
            DOM.copyBtn.style.background = '';
        }, 2000);
    } catch (error) {
        console.error('❌ Copy failed:', error);
        fallbackCopyToClipboard(code);
    }
}

function fallbackCopyToClipboard(text) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.select();
    
    try {
        document.execCommand('copy');
        console.log('📋 Code copied (fallback)');
        DOM.copyBtn.innerHTML = '✓';
    } catch (error) {
        console.error('❌ Fallback copy failed:', error);
    }
    
    document.body.removeChild(textArea);
}

// ============================================
// 14. START APPLICATION
// ============================================

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}

console.log('🎊 Yalda Project Script Loaded');
