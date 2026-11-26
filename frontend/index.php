<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LC Attends | Clock in</title>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="index.php" aria-label="LC Attends home">
            <svg class="brand-mark" viewBox="0 0 70 42" aria-hidden="true">
                <path fill="#536719" d="M0 17 18 0l17 17v25H0zM35 17 52 0l18 17v25H35z"/>
                <path fill="#a9e900" d="M8 14 23 0l14 14v28H8z"/>
                <path fill="#ffffff" d="M18 11 28 0l10 11v31H18zM34 11 44 0l10 11v31H34z"/>
                <path fill="#17242a" d="M25 15h6v27h-6zm16 0h6v27h-6z"/>
            </svg>
            <span><strong>LC</strong> <em>Attends</em></span>
        </a>
    </header>

    <main class="clock-page">
        <section class="welcome-row" aria-label="Welcome and shift status">
            <div class="welcome">
                <svg class="clock-face" viewBox="0 0 140 140" role="img" aria-label="Clock">
                    <circle cx="70" cy="70" r="68" fill="#2f707a"/>
                    <circle cx="70" cy="70" r="49" fill="none" stroke="#fff" stroke-width="3"/>
                    <path d="M70 35v37l22 13" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div class="welcome-copy">
                    <p class="greeting" id="greeting">Good day!</p>
                    <h1>Welcome, <span>{Employee}</span></h1>
                    <p class="date-line" id="date-line"></p>
                </div>
            </div>

            <div class="status-panel" aria-hidden="true"></div>
        </section>

        <section class="clock-action" aria-label="Clock-in control">
            <button id="clock-in-button" class="clock-in-button" type="button">
                <span class="play-icon" aria-hidden="true"></span>
                <span>Clock In</span>
            </button>
            <p id="clock-feedback" class="feedback" role="status" aria-live="polite"></p>
            <p class="location-note">
                <span aria-hidden="true">ⓘ</span>
                Your live location is being recorded, please reach<br class="desktop-break">
                out to your supervisor for more information
            </p>
        </section>
    </main>

    <script src="assets/js/clock-in.js" defer></script>
</body>
</html>
