// version: 0.3.3 - Internationalization support added
// ========== Global Variables ==========

let userId = '';
let giftCount = 0;
let gameActive = false;
let gameStarted = false; // Has the game started?
let fruitSpawnInterval;
let preGameSpawnInterval;
let translations = {}; // Global translations object

// DOM Elements - IDs updated to match HTML
const welcomeScreen = document.getElementById('id-input-section');
const gameScreen = document.getElementById('catch-fruit-section');
const guideModal = document.getElementById('start-game-section');
const giftModal = document.getElementById('gift-section');
const loading = document.getElementById('loading-indicator');
const userIdInput = document.getElementById('telegram-id');
const startBtn = document.getElementById('confirm-btn');
const startGameBtn = document.getElementById('start-game-btn');
const continueBtn = document.getElementById('copy-btn');
const gameArea = document.body; // Fruits fall across entire body
const displayUserId = document.getElementById('displayUserId'); // May not exist in new HTML
const giftCountDisplay = document.getElementById('giftCount'); // May not exist  
// particleContainer removed - particles always added to document.body
const giftTitle = document.querySelector('.gift-message');
const giftCode = document.getElementById('gift-code');
const giftDescription = document.getElementById('gift-description');

// Game settings
const FRUIT_TYPES = {
    pomegranate: { emoji: '🍎', color: '#c62828', particle: '🔴' },
    watermelon: { emoji: '🍉', color: '#2e7d32', particle: '🟢' }
};

const CONFIG = {
    preGameInterval: 1200,     // falling speed before game (50% faster)
    gameInterval: 1800,        // falling speed during game
    initialInterval: 2000,     // initial falling speed
    fruitFallDuration: [5000, 8000],  // fall duration (random)
    maxFruitsOnScreen: 15,
    maxInitialFruits: 8,       // max fruits in initial fall
    fruitSize: 60              // fruit size in pixels
};

// Event listeners will be added in DOMContentLoaded below

// ========== Internationalization Functions ==========
async function loadTranslations() {
    try {
        const currentLang = document.documentElement.getAttribute('data-lang') || 'en';
        const response = await fetch(`/api/admin-api.php?action=get_translations&lang=${currentLang}`);
        const data = await response.json();

        if (data.success) {
            translations = data.translations;
            console.log('Translations loaded for language:', currentLang);
            updateUIText();
        } else {
            console.error('Failed to load translations:', data);
        }
    } catch (error) {
        console.error('Error loading translations:', error);
    }
}

function t(key, placeholders = {}) {
    let text = translations[key] || key;

    // Replace placeholders
    Object.keys(placeholders).forEach(placeholder => {
        text = text.replace(new RegExp(`{${placeholder}}`, 'g'), placeholders[placeholder]);
    });

    return text;
}

function updateUIText() {
    // Update page title and description
    const titleElement = document.getElementById('page-title');
    if (titleElement) {
        titleElement.textContent = t('yalda_festival') + ' - ' + t('festival_system');
    }

    const descElement = document.getElementById('page-description');
    if (descElement) {
        descElement.content = t('yalda_night_festival') + ' - ' + t('ready_receive_gift');
    }

    // Update other UI elements that have text content
    const elements = document.querySelectorAll('[data-translate]');
    elements.forEach(element => {
        const key = element.getAttribute('data-translate');
        if (key && translations[key]) {
            element.textContent = t(key);
        }
    });

    // Update placeholders
    const inputs = document.querySelectorAll('[data-translate-placeholder]');
    inputs.forEach(input => {
        const key = input.getAttribute('data-translate-placeholder');
        if (key && translations[key]) {
            input.placeholder = t(key);
        }
    });
}

// ========== Paste button functionality ==========
const pasteBtn = document.getElementById('paste-btn');
if (pasteBtn) {
    pasteBtn.addEventListener('click', async () => {
        try {
            const text = await navigator.clipboard.readText();
            userIdInput.value = text.trim();
            userIdInput.dispatchEvent(new Event('input'));
            showValidationMessage('ID pasted from clipboard', 'success');
        } catch (error) {
            console.error('Paste error:', error);
            showValidationMessage('Clipboard access error', 'error');
        }
    });
}

// ========== Help button functionality ==========
const helpBtn = document.getElementById('help-btn');
if (helpBtn) {
    helpBtn.addEventListener('click', () => {
        const helpContent = document.getElementById('help-content');
        if (helpContent) {
            helpContent.classList.toggle('show');
        }
    });
}

// ========== Validation message display ==========
function showValidationMessage(message, type = 'info') {
    const validationEl = document.getElementById('validation-message');
    if (validationEl) {
        validationEl.textContent = message;
        validationEl.className = 'validation-message ' + type;
    }
}

// ========== User Entry Management ==========
startBtn.addEventListener('click', async () => {
    const inputUserId = userIdInput.value.trim();
    if (!inputUserId) {
        showValidationMessage('Please enter your User ID', 'error');
        return;
    }

    // Show loading state
    startBtn.disabled = true;
    startBtn.textContent = 'Checking...';
    showValidationMessage('Checking User ID...', 'info');

    try {
        // Validate user with bot API
        const response = await fetch('/api/validate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ telegram_id: inputUserId })
        });

        const data = await response.json();

        if (data.success) {
            // User is valid
            userId = inputUserId;
            showValidationMessage('User ID is valid ✓', 'success');

            // Hide welcome, show guide after short delay
            setTimeout(() => {
                welcomeScreen.classList.remove('active');
                guideModal.classList.add('active');

                // Hide main modal so fruits are better visible
                const glassCard = document.getElementById('glass-card');
                if (glassCard) {
                    glassCard.style.opacity = '0.7'; // semi-transparent
                }

                // Stop initial fall and start pre-game fall
                if (window.initialFruitInterval) {
                    clearInterval(window.initialFruitInterval);
                }
                startPreGameFruits();  // start non-interactive fruit fall
            }, 1000);

        } else {
            // User validation failed
            showValidationMessage(data.message || 'Invalid User ID', 'error');
            startBtn.disabled = false;
            startBtn.textContent = 'Enter the Festival';
        }

    } catch (error) {
        console.error('Validation error:', error);
        showValidationMessage('Connection error', 'error');
        startBtn.disabled = false;
        startBtn.textContent = 'Enter the Festival';
    }
});

// ========== Start Game ==========
startGameBtn.addEventListener('click', () => {
    guideModal.classList.remove('active');
    gameScreen.classList.add('active');
    gameActive = true;
    gameStarted = true; // game started
    clearInterval(preGameSpawnInterval);  // stop pre-game fall
    startGameFruits();  // start interactive fruit fall
});

continueBtn.addEventListener('click', () => {
    // Copy code to clipboard
    const code = giftCode.textContent;
    navigator.clipboard.writeText(code).then(() => {
        alert('Gift code copied: ' + code);
    }).catch(err => {
        console.error('Copy failed:', err);
    });
});

// ========== Fruit fall before game start ==========
function startPreGameFruits() {
    const container = document.getElementById('fruits-container');
    if (!container) return;

    preGameSpawnInterval = setInterval(() => {
        if (container.querySelectorAll('.fruit').length < CONFIG.maxFruitsOnScreen) {
            spawnFruit(false, container);  // non-clickable fruit
        }
    }, CONFIG.preGameInterval);
}

// ========== Initial fruit fall from page start ==========
function startInitialFruitRain() {
    const container = document.getElementById('fruits-container');
    if (!container) {
        console.error('Fruits container not found!');
        return;
    }

    console.log('🎪 Starting initial fruit rain...');

    // Initial fall with slower speed - clickable for demo
    const initialInterval = setInterval(() => {
        if (container.querySelectorAll('.fruit').length < CONFIG.maxInitialFruits) {
            console.log('Spawning initial demo fruit...');
            spawnFruit(true, container);  // clickable fruit for demo
        }
    }, CONFIG.initialInterval);

    // Save interval for later cleanup
    window.initialFruitInterval = initialInterval;
}

// ========== Fruit fall during game ==========
function startGameFruits() {
    const container = document.getElementById('fruits-container');
    if (!container) return;
    
    fruitSpawnInterval = setInterval(() => {
        if (container.querySelectorAll('.fruit').length < CONFIG.maxFruitsOnScreen) {
            spawnFruit(true, container);  // clickable fruit
        }
    }, CONFIG.gameInterval);
}

// ========== Create fruit ==========
function spawnFruit(interactive, container) {
    // Use theme objects if available, otherwise fall back to default
    let fruitEmoji, fruitType;

    if (window.activeTheme && window.activeTheme.fallingObjects && window.activeTheme.fallingObjects.length > 0) {
        const objects = window.activeTheme.fallingObjects;
        fruitEmoji = objects[Math.floor(Math.random() * objects.length)];
        fruitType = fruitEmoji.includes('🍎') || fruitEmoji.includes('🍉') ? 'pomegranate' :
                   fruitEmoji.includes('❄') ? 'snow' : 'pomegranate';
    } else {
        // Default behavior
        fruitType = Math.random() > 0.5 ? 'pomegranate' : 'watermelon';
        const fruit = FRUIT_TYPES[fruitType];
        fruitEmoji = fruit.emoji;
    }
    
    const fruitEl = document.createElement('div');
    fruitEl.className = 'fruit';
    fruitEl.textContent = fruitEmoji;
    fruitEl.style.position = 'absolute';
    fruitEl.style.left = Math.random() * (window.innerWidth - CONFIG.fruitSize) + 'px';
    fruitEl.style.top = '-100px';
    fruitEl.style.fontSize = CONFIG.fruitSize + 'px';
    fruitEl.style.zIndex = '10';
    fruitEl.style.userSelect = 'none';
    fruitEl.dataset.type = fruitType;
    
    const fallDuration = Math.random() * 
        (CONFIG.fruitFallDuration[1] - CONFIG.fruitFallDuration[0]) + 
        CONFIG.fruitFallDuration[0];
    
    fruitEl.style.animation = `fall ${fallDuration}ms linear`;
    
    // Make fruits clickable - demo effect before game, prize during game
    fruitEl.style.cursor = 'pointer';
    fruitEl.style.pointerEvents = 'auto'; // ensure clickability
    fruitEl.style.zIndex = '1000'; // above modal

    // Add event listener for all fruits
    fruitEl.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        console.log('Fruit clicked - Type:', fruitType, 'Game active:', gameActive, 'Game started:', gameStarted);

        if (gameActive && gameStarted) {
            // Game mode - can win prizes
            handleFruitClick(fruitEl, fruit);
        } else {
            // Demo mode - just show particles
            handleDemoFruitClick(fruitEl, fruitType);
        }
    });
    
    container.appendChild(fruitEl);
    
    setTimeout(() => {
        if (fruitEl.parentNode) fruitEl.remove();
    }, fallDuration);
}

// ========== Fruit click (demo before game) ==========
function handleDemoFruitClick(fruitEl, fruitType) {
    console.log('🎯 Demo fruit clicked:', fruitType);

    // Stop the falling animation
    fruitEl.style.animation = 'none';

    // Add shatter effect
    fruitEl.classList.add(`shatter-${fruitType}`);
    console.log('Added shatter class:', `shatter-${fruitType}`);

    // Create particles - using viewport position
    const rect = fruitEl.getBoundingClientRect();
    const fruitCenterX = rect.left + rect.width/2;
    const fruitCenterY = rect.top + rect.height/2;

    console.log('Creating particles at viewport position:', fruitCenterX, fruitCenterY, 'rect:', rect);
    // #region agent log
    fetch('http://127.0.0.1:7243/ingest/6b0f03cd-3003-4705-bb7e-0afe60440707',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'js/game-v0.3.js:285',message:'Demo fruit click - calculating position',data:{fruitType:fruitType,rect:{left:rect.left,top:rect.top,width:rect.width,height:rect.height},fruitCenterX:fruitCenterX,fruitCenterY:fruitCenterY,scrollY:window.scrollY,scrollX:window.scrollX},timestamp:Date.now(),sessionId:'debug-session',runId:'initial'})}).catch(()=>{});
    // #endregion
    createDemoParticles(fruitCenterX, fruitCenterY, fruitType);

    // Remove fruit after animation
    setTimeout(() => {
        if (fruitEl.parentNode) {
            fruitEl.remove();
            console.log('Fruit removed');
        }
    }, 800);

    // Add screen shake effect
    document.body.style.animation = 'shake 0.5s ease-in-out';
    setTimeout(() => {
        document.body.style.animation = '';
    }, 500);
}

// ========== Fruit click ==========
function handleFruitClick(fruitEl, fruit) {
    if (!gameActive) return;

    // Stop the game temporarily
    gameActive = false;
    clearInterval(fruitSpawnInterval);

    // Enhanced explosion animation based on fruit type
    const fruitType = fruitEl.dataset.type || 'pomegranate';
    fruitEl.classList.add(`shatter-${fruitType}`);
    fruitEl.style.pointerEvents = 'none';

    // Create enhanced particles - using viewport position for particles
    const rect = fruitEl.getBoundingClientRect();
    const gameParticleX = rect.left + rect.width/2;
    const gameParticleY = rect.top + rect.height/2;
    // #region agent log
    fetch('http://127.0.0.1:7243/ingest/6b0f03cd-3003-4705-bb7e-0afe60440707',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'js/game-v0.3.js:321',message:'Game fruit click - calculating position',data:{fruitType:fruitType,offsetLeft:fruitEl.offsetLeft,offsetTop:fruitEl.offsetTop,offsetWidth:fruitEl.offsetWidth,offsetHeight:fruitEl.offsetHeight,gameParticleX:gameParticleX,gameParticleY:gameParticleY,fruitRect:fruitEl.getBoundingClientRect()},timestamp:Date.now(),sessionId:'debug-session',runId:'initial'})}).catch(()=>{});
    // #endregion
    createEnhancedParticles(gameParticleX, gameParticleY, fruitType);

    // Remove fruit after animation
    setTimeout(() => {
        if (fruitEl.parentNode) {
            fruitEl.remove();
        }
    }, 800);

    // Get gift from API
    fetchGift();
}

// ========== Create demo particles (before game) ==========
function createDemoParticles(x, y, fruitType) {
    // Always add to document.body because particles have position: fixed
    const container = document.body;

    const particleCount = fruitType === 'snow' ? 12 : 8; // less than main game

    console.log('Creating demo particles:', particleCount, 'for type:', fruitType, 'in container:', container.tagName);
    // #region agent log
    fetch('http://127.0.0.1:7243/ingest/6b0f03cd-3003-4705-bb7e-0afe60440707',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'js/game-v0.3.js:338',message:'Creating demo particles - start',data:{x:x,y:y,fruitType:fruitType,particleCount:particleCount,containerTagName:container.tagName,viewport:{width:window.innerWidth,height:window.innerHeight}},timestamp:Date.now(),sessionId:'debug-session',runId:'initial'})}).catch(()=>{});
    // #endregion

    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'seed-particle demo-particle';

        // Choose particle type based on fruit
        switch(fruitType) {
            case 'pomegranate':
                particle.classList.add('seed-pomegranate');
                particle.textContent = '•';
                break;
            case 'watermelon':
                particle.classList.add('seed-watermelon');
                particle.textContent = '●';
                break;
            case 'snow':
                particle.classList.add('seed-snow');
                particle.textContent = '❄';
                break;
            default:
                particle.classList.add('seed-pomegranate');
                particle.textContent = '•';
        }

        // Always use fixed position because container is fixed
        particle.style.position = 'fixed';
        particle.style.left = x + 'px';
        particle.style.top = y + 'px';
        console.log('Using fixed position for particle at:', x, y);
        // #region agent log
        fetch('http://127.0.0.1:7243/ingest/6b0f03cd-3003-4705-bb7e-0afe60440707',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'js/game-v0.3.js:370',message:'Setting particle position',data:{particleIndex:i,position:{left:x,top:y},stylePosition:particle.style.position,particleType:particle.className},timestamp:Date.now(),sessionId:'debug-session',runId:'initial'})}).catch(()=>{});
        // #endregion

        // Use !important to prevent override by CSS
        particle.style.cssText = `
            position: fixed !important;
            left: ${x}px !important;
            top: ${y}px !important;
            z-index: 10000 !important;
            pointer-events: none !important;
            animation: seedBurst 1.2s ease-out forwards !important;
        `;

        // Calculate explosion trajectory
        const angle = (Math.PI * 2 * i) / particleCount + (Math.random() - 0.5) * 0.5;
        const distance = 80 + Math.random() * 40; // smaller than main game
        const tx = Math.cos(angle) * distance;
        const ty = Math.sin(angle) * distance;

        particle.style.setProperty('--tx', tx + 'px');
        particle.style.setProperty('--ty', ty + 'px');

        console.log('Adding demo particle to container:', container.id || 'body', 'position:', particle.style.position);
        container.appendChild(particle);

        // Remove particle after animation
        setTimeout(() => {
            if (particle.parentNode) {
                particle.remove();
                console.log('Demo particle removed');
            }
        }, 1500); // shorter than main game
    }
}

// ========== Create enhanced explosion particles ==========
function createEnhancedParticles(x, y, fruitType) {
    // Always add to document.body because particles have position: fixed
    const container = document.body;
    // #region agent log
    fetch('http://127.0.0.1:7243/ingest/6b0f03cd-3003-4705-bb7e-0afe60440707',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'js/game-v0.3.js:402',message:'Creating enhanced particles - start',data:{x:x,y:y,fruitType:fruitType,containerId:container.id,containerTagName:container.tagName},timestamp:Date.now(),sessionId:'debug-session',runId:'initial'})}).catch(()=>{});
    // #endregion

    // Use theme explosion effect if available
    const explosionEffect = window.activeTheme ? window.activeTheme.explosionEffect : null;

    let particleCount, particleType;
    if (explosionEffect === 'snow') {
        particleCount = 16;
        particleType = 'snow';
    } else if (explosionEffect === 'sparkles') {
        particleCount = 20;
        particleType = 'sparkles';
    } else {
        // Default to seeds based on fruit type
        particleCount = fruitType === 'snow' ? 16 : 12;
        particleType = fruitType;
    }

    for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.className = 'seed-particle';

        // Choose particle type based on explosion effect
        switch(particleType) {
            case 'pomegranate':
                particle.classList.add('seed-pomegranate');
                particle.textContent = '•';
                break;
            case 'watermelon':
                particle.classList.add('seed-watermelon');
                particle.textContent = '●';
                break;
            case 'snow':
                particle.classList.add('seed-snow');
                particle.textContent = '❄';
                break;
            case 'sparkles':
                particle.classList.add('seed-sparkle');
                particle.textContent = '✨';
                break;
            default:
                particle.classList.add('seed-pomegranate');
                particle.textContent = '•';
        }

        // Use !important to prevent override by CSS
        particle.style.cssText = `
            position: fixed !important;
            left: ${x}px !important;
            top: ${y}px !important;
            z-index: 10000 !important;
            pointer-events: none !important;
            animation: seedBurst 1.2s ease-out forwards !important;
        `;
        // #region agent log
        fetch('http://127.0.0.1:7243/ingest/6b0.0.1:7243/ingest/6b0f03cd-3003-4705-bb7e-0afe60440707',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({location:'js/game-v0.3.js:448',message:'Setting enhanced particle position',data:{particleIndex:i,position:{left:x,top:y},stylePosition:particle.style.position,particleType:particle.className},timestamp:Date.now(),sessionId:'debug-session',runId:'initial'})}).catch(()=>{});
        // #endregion

        // Calculate explosion trajectory
        const angle = (Math.PI * 2 * i) / particleCount + (Math.random() - 0.5) * 0.5;
        const distance = 120 + Math.random() * 80;
        const tx = Math.cos(angle) * distance;
        const ty = Math.sin(angle) * distance;

        particle.style.setProperty('--tx', tx + 'px');
        particle.style.setProperty('--ty', ty + 'px');

        container.appendChild(particle);

        // Remove particle after animation
        setTimeout(() => {
            if (particle.parentNode) {
                particle.remove();
            }
        }, 2000);
    }

    // Add screen shake effect for dramatic explosions
    document.body.style.animation = 'shake 0.5s ease-in-out';
    setTimeout(() => {
        document.body.style.animation = '';
    }, 500);
}

// ========== Screen shake animation ==========
if (!document.querySelector('#shake-animation')) {
    const style = document.createElement('style');
    style.id = 'shake-animation';
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-2px); }
            20%, 40%, 60%, 80% { transform: translateX(2px); }
        }
    `;
    document.head.appendChild(style);
}

// ========== Get gift from API ==========
async function fetchGift() {
    // Show loading
    if (loading) loading.style.display = 'flex';
    
    // Hide game screen
    if (gameScreen) gameScreen.classList.remove('active');
    
    try {
        const response = await fetch('/api/participate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ user_id: userId })
        });
        
        const data = await response.json();

        if (loading) loading.style.display = 'none';

        if (data.status === 'ok' && data.prize) {
            // Handle different prize types
            if (data.prize.code) {
                // Won a prize
                showGiftModal({
                    code: data.prize.code,
                    title: data.prize.name || 'Discount Code',
                    description: `${data.prize.percent}% discount code`,
                    percent: data.prize.percent
                });
            } else if (data.status === 'respin') {
                // Try again
                alert('Try again! You can participate again.');
                if (gameScreen) gameScreen.classList.add('active');
                gameActive = true;
                startGameFruits();
            } else {
                // No prize
                alert(data.prize.name || 'Unfortunately, no prize this time');
                // Return to game
                if (gameScreen) gameScreen.classList.add('active');
                gameActive = true;
                startGameFruits();
            }
        } else {
            alert(data.message || 'Error receiving gift');
            // Return to game
            if (gameScreen) gameScreen.classList.add('active');
            gameActive = true;
            startGameFruits();
        }
    } catch (error) {
        if (loading) loading.style.display = 'none';
        console.error('API Error:', error);
        alert('Connection error');
        // Return to game
        if (gameScreen) gameScreen.classList.add('active');
        gameActive = true;
        startGameFruits();
    }
}

// ========== Show gift modal ==========
function showGiftModal(gift) {
    // Update gift info
    if (giftCode) giftCode.textContent = gift.code;

    // Show description if available
    if (gift.description && giftDescription) {
        giftDescription.textContent = gift.description;
        giftDescription.style.display = 'block';
    } else if (giftDescription) {
        giftDescription.style.display = 'none';
    }

    // Show gift modal
    if (giftModal) giftModal.classList.add('active');
}

// ========== CSS Animations (fallback if not in CSS) ==========
if (!document.querySelector('#dynamic-animations')) {
    const style = document.createElement('style');
    style.id = 'dynamic-animations';
    style.textContent = `
        @keyframes fall {
            from { top: -100px; transform: rotate(0deg); }
            to { top: 100vh; transform: rotate(360deg); }
        }
        
        @keyframes explode {
            to {
                transform: translate(var(--tx), var(--ty));
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// ========== Language Toggle ==========
const langToggle = document.getElementById('lang-toggle');
if (langToggle) {
    langToggle.addEventListener('click', () => {
        const html = document.documentElement;
        const currentLang = html.getAttribute('data-lang') || 'fa';

        if (currentLang === 'fa') {
            html.setAttribute('data-lang', 'en');
            html.setAttribute('dir', 'ltr');
        } else {
            html.setAttribute('data-lang', 'fa');
            html.setAttribute('dir', 'rtl');
        }
    });
}

// ========== Theme Loading ==========
async function loadActiveTheme() {
    try {
        const response = await fetch('api/admin-api.php?action=get_themes');
        const data = await response.json();

        if (data.success && data.data) {
            const activeTheme = data.data.find(theme => theme.active);
            if (activeTheme) {
                applyTheme(activeTheme);
            }
        }
    } catch (error) {
        console.log('Theme loading failed, using defaults:', error);
    }
}

function applyTheme(theme) {
    // Apply background
    if (theme.background_path) {
        document.body.style.backgroundImage = `url('${theme.background_path}')`;
    }

    // Apply colors to UI elements - use transparent/white colors for glass effect
    const colors = JSON.parse(theme.color_palette || '["rgba(255,255,255,0.3)", "rgba(255,255,255,0.1)"]');

    // Update CSS custom properties for dynamic theming - glass effect colors
    document.documentElement.style.setProperty('--primary-color', colors[0] || 'rgba(255,255,255,0.3)');
    document.documentElement.style.setProperty('--secondary-color', colors[1] || 'rgba(255,255,255,0.1)');

    // Update specific elements - colors are handled by CSS custom properties now
    console.log('Applying theme colors:', colors);

    // Update button gradients - use glass colors
    const buttons = document.querySelectorAll('.btn-primary');
    buttons.forEach(btn => {
        btn.style.background = `linear-gradient(135deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.1) 100%)`;
        btn.style.border = `1px solid rgba(255,255,255,0.2)`;
        btn.style.backdropFilter = `blur(10px)`;
    });

    // Store falling objects for game
    if (theme.falling_objects) {
        window.activeTheme = {
            fallingObjects: JSON.parse(theme.falling_objects),
            explosionEffect: theme.explosion_effect,
            guideText: theme.guide_text
        };
    }

    console.log('Theme applied:', theme.name);
}

// ========== Auto start ==========
document.addEventListener('DOMContentLoaded', async function() {
    console.log('🎪 Festival Game v0.3.3 - Loading...');

    // Load translations first
    await loadTranslations();

    // Test basic functionality
    console.log('✅ DOM Content Loaded');
    console.log('User ID input element:', userIdInput);
    console.log('Start button element:', startBtn);
    console.log('Fruits container:', document.getElementById('fruits-container'));

    // Enable button when valid input
    userIdInput.addEventListener('input', () => {
        const value = userIdInput.value.trim();
        if (value && value.length >= 5 && !isNaN(value)) {
            startBtn.disabled = false;
        } else {
            startBtn.disabled = true;
        }
    });

    // Load theme on startup and start initial fruit rain
    loadActiveTheme();
    startInitialFruitRain();

    // Add a visible test to ensure JS is working
    const testDiv = document.createElement('div');
    testDiv.id = 'js-test-indicator';
    testDiv.style.position = 'fixed';
    testDiv.style.top = '10px';
    testDiv.style.right = '10px';
    testDiv.style.background = 'rgba(0,255,0,0.8)';
    testDiv.style.color = 'white';
    testDiv.style.padding = '5px 10px';
    testDiv.style.borderRadius = '5px';
    testDiv.style.fontSize = '12px';
    testDiv.style.zIndex = '9999';
    testDiv.textContent = t('js_working');
    document.body.appendChild(testDiv);

    setTimeout(() => {
        testDiv.remove();
    }, 3000);
});
