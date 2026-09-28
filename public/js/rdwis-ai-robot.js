/**
 * RDWIS AI ASSISTANT (RIVA) — ROBOT MASCOT CONTROLLER
 * Handles Eye Tracking (with CSS Zoom compensation), Natural Blinking,
 * Arm Greeting Waves, 5-Minute Idle 360° Spin, and State Transitions.
 * 
 * Zero external dependencies. 100% Offline Compatible.
 */
(function() {
    'use strict';

    if (window.__RDWIS_AI_ROBOT_INITIALIZED__) return;
    window.__RDWIS_AI_ROBOT_INITIALIZED__ = true;

    // Configuration Constants
    var IDLE_SPIN_INTERVAL = 180000; // 3 minutes in milliseconds
    var SPEECH_BUBBLE_AUTO_DISMISS = 7000; // 7 seconds
    var EYE_MAX_X = 5.5; // Max horizontal pupil delta (px)
    var EYE_MAX_Y = 3.5; // Max vertical pupil delta (px)
    var LERP_FACTOR = 0.15; // Damping interpolation

    // State Variables
    var lastActivityTime = Date.now();
    var isChatOpen = false;
    var isSpinning = false;
    var isMascotHovered = false;
    var isMouseInViewport = false;
    var speechBubbleTimer = null;
    var blinkTimer = null;
    var spinCheckInterval = null;

    // Target and Current Eye Tracking Coordinates
    var targetEyeX = 0;
    var targetEyeY = 0;
    var currentEyeX = 0;
    var currentEyeY = 0;
    var targetHeadAngle = 0;
    var currentHeadAngle = 0;
    var isTrackingRafActive = false;

    // DOM References (Cached once DOM is ready)
    var launcherBtn = null;
    var speechBubble = null;
    var headRig = null;
    var eyeLeft = null;
    var eyeRight = null;
    var armLeft = null;
    var mouthRig = null;
    var floatRig = null;
    var pwaBanner = null;

    /**
     * Retrieve the active CSS zoom factor on document.body or html
     * Handles zoom-scale.css (body { zoom: 0.85/0.75 }) and Firefox transforms.
     */
    function getBodyZoomFactor() {
        try {
            var bodyStyle = window.getComputedStyle(document.body);
            var zoom = parseFloat(bodyStyle.zoom);
            if (!isNaN(zoom) && zoom > 0) {
                return zoom;
            }
        } catch(e) {}
        return 1.0;
    }

    /**
     * Initialize DOM elements
     */
    function initElements() {
        launcherBtn = document.getElementById('rdwisRobotLauncher') || document.getElementById('rdwisAiToggleBtn');
        speechBubble = document.getElementById('rdwisSpeechBubble');
        headRig = document.getElementById('robot-head');
        eyeLeft = document.getElementById('eye-left');
        eyeRight = document.getElementById('eye-right');
        armLeft = document.getElementById('robot-arm-left');
        mouthRig = document.getElementById('robot-mouth');
        floatRig = document.getElementById('robot-float-rig');
        pwaBanner = document.getElementById('pwa-install-banner');

        return !!(launcherBtn && headRig && eyeLeft && eyeRight);
    }

    /**
     * Mouse Movement & Eye Tracking Loop
     */
    function updateEyeTracking(e) {
        lastActivityTime = Date.now();
        if (isSpinning) return;

        isMouseInViewport = true;
        var zoom = getBodyZoomFactor();

        // Get coordinates of the robot visor center
        var headRect = headRig.getBoundingClientRect();
        var headCenterX = (headRect.left + headRect.width / 2);
        var headCenterY = (headRect.top + headRect.height / 2);

        // Normalize client coordinates against body zoom
        var mouseX = e.clientX / zoom;
        var mouseY = e.clientY / zoom;

        // Calculate vector from head center to cursor
        var dx = mouseX - headCenterX;
        var dy = mouseY - headCenterY;
        var distance = Math.sqrt(dx * dx + dy * dy);

        if (distance > 0) {
            // Clamped orbital translation for the eyes
            var angle = Math.atan2(dy, dx);
            var intensity = Math.min(distance / 250, 1.0);

            targetEyeX = Math.cos(angle) * (EYE_MAX_X * intensity);
            targetEyeY = Math.sin(angle) * (EYE_MAX_Y * intensity);

            // Subtle head tilt: -4deg (far left) to +4deg (far right)
            var horizontalRatio = Math.max(-1, Math.min(1, dx / 400));
            targetHeadAngle = horizontalRatio * 4.0;
        }

        if (!isTrackingRafActive) {
            isTrackingRafActive = true;
            requestAnimationFrame(renderTrackingStep);
        }
    }

    /**
     * Render Step using Linear Interpolation (LERP) for 60 FPS fluidity
     */
    function renderTrackingStep() {
        if (!isMouseInViewport && !isChatOpen) {
            // Slowly return eyes and head to neutral center
            targetEyeX = 0;
            targetEyeY = 0;
            targetHeadAngle = 0;
        }

        // Apply LERP
        currentEyeX += (targetEyeX - currentEyeX) * LERP_FACTOR;
        currentEyeY += (targetEyeY - currentEyeY) * LERP_FACTOR;
        currentHeadAngle += (targetHeadAngle - currentHeadAngle) * LERP_FACTOR;

        // Apply eye transforms
        if (eyeLeft && eyeRight) {
            var eyeTransform = 'translate(' + currentEyeX.toFixed(2) + 'px, ' + currentEyeY.toFixed(2) + 'px)';
            eyeLeft.style.transform = eyeTransform;
            eyeRight.style.transform = eyeTransform;
        }

        // Apply head tilt
        if (headRig && !isSpinning) {
            headRig.style.transform = 'rotate(' + currentHeadAngle.toFixed(2) + 'deg)';
        }

        // Stop RAF if values have settled close to target
        var diff = Math.abs(targetEyeX - currentEyeX) + Math.abs(targetEyeY - currentEyeY);
        if (diff > 0.05 || isMouseInViewport) {
            requestAnimationFrame(renderTrackingStep);
        } else {
            isTrackingRafActive = false;
        }
    }

    /**
     * Natural Eye Blinking Logic
     */
    function scheduleNextBlink() {
        if (blinkTimer) clearTimeout(blinkTimer);

        // Random interval between 3.5s and 6.5s
        var nextInterval = 3500 + Math.random() * 3000;
        blinkTimer = setTimeout(function() {
            triggerBlink(function() {
                // 25% chance of an adorable double-blink
                if (Math.random() < 0.25) {
                    setTimeout(function() {
                        triggerBlink(scheduleNextBlink);
                    }, 120);
                } else {
                    scheduleNextBlink();
                }
            });
        }, nextInterval);
    }

    function triggerBlink(callback) {
        if (document.hidden || isSpinning || !launcherBtn) {
            if (callback) callback();
            return;
        }

        launcherBtn.classList.add('riva-blinking');
        setTimeout(function() {
            if (launcherBtn) launcherBtn.classList.remove('riva-blinking');
            if (callback) callback();
        }, 160);
    }

    /**
     * Arm Waving Greeting Animation
     */
    function triggerGreetingWave() {
        if (!launcherBtn || isSpinning) return;
        launcherBtn.classList.add('riva-waving');
        setTimeout(function() {
            if (launcherBtn) launcherBtn.classList.remove('riva-waving');
        }, 2800);
    }

    /**
     * Speech Bubble Controls
     */
    function showSpeechBubble(autoDismiss) {
        if (!speechBubble || isChatOpen) return;
        speechBubble.classList.add('is-visible');

        if (speechBubbleTimer) clearTimeout(speechBubbleTimer);
        if (autoDismiss) {
            speechBubbleTimer = setTimeout(function() {
                hideSpeechBubble();
            }, SPEECH_BUBBLE_AUTO_DISMISS);
        }
    }

    function hideSpeechBubble() {
        if (!speechBubble) return;
        speechBubble.classList.remove('is-visible');
        if (speechBubbleTimer) {
            clearTimeout(speechBubbleTimer);
            speechBubbleTimer = null;
        }
    }

    /**
     * Stylized 5-Minute 360° Spin
     */
    function trigger5MinuteSpin() {
        // Strict safety guards
        if (isSpinning || isChatOpen || isMascotHovered || document.hidden || !launcherBtn) {
            return;
        }

        isSpinning = true;
        hideSpeechBubble();

        // Center eyes and tilt before spinning
        if (eyeLeft) eyeLeft.style.transform = 'translate(0, 0)';
        if (eyeRight) eyeRight.style.transform = 'translate(0, 0)';
        if (headRig) headRig.style.transform = 'rotate(0deg)';

        launcherBtn.classList.add('riva-spin-active');

        // Spin animation duration matches CSS keyframe (1.8s)
        setTimeout(function() {
            if (launcherBtn) launcherBtn.classList.remove('riva-spin-active');
            isSpinning = false;
            lastActivityTime = Date.now(); // Reset idle clock
        }, 1850);
    }

    /**
     * Idle Activity Checker
     */
    function startIdleTracker() {
        if (spinCheckInterval) clearInterval(spinCheckInterval);

        // Check idle time every 10 seconds
        spinCheckInterval = setInterval(function() {
            var elapsed = Date.now() - lastActivityTime;
            if (elapsed >= IDLE_SPIN_INTERVAL) {
                trigger5MinuteSpin();
            }
        }, 10000);
    }

    /**
     * Synchronize PWA Install Banner offset
     */
    function checkPwaBannerCollision() {
        if (!pwaBanner) pwaBanner = document.getElementById('pwa-install-banner');
        if (pwaBanner && pwaBanner.style.display !== 'none' && pwaBanner.style.bottom === '20px') {
            document.body.classList.add('has-pwa-banner');
        } else {
            document.body.classList.remove('has-pwa-banner');
        }
    }

    /**
     * Public Robot Mood Controller (Called by Chat Engine)
     */
    window.RivaRobot = {
        setMood: function(mood) {
            if (!launcherBtn) return;
            launcherBtn.classList.remove('riva-state-thinking', 'riva-state-talking');

            if (mood === 'thinking') {
                launcherBtn.classList.add('riva-state-thinking');
            } else if (mood === 'talking') {
                launcherBtn.classList.add('riva-state-talking');
            }
        },

        setChatOpen: function(open) {
            isChatOpen = open;
            if (open) {
                hideSpeechBubble();
            }
        },

        wave: function() {
            triggerGreetingWave();
        },

        showBubble: function(text) {
            if (speechBubble && text) {
                var textEl = speechBubble.querySelector('.rdwis-bubble-text');
                if (textEl) textEl.textContent = text;
            }
            showSpeechBubble(true);
        }
    };

    /**
     * Main Initialization on DOM Ready
     */
    function init() {
        if (!initElements()) {
            setTimeout(init, 100);
            return;
        }

        // Global Activity Listeners to Reset Idle Timer
        var activityEvents = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll'];
        activityEvents.forEach(function(evt) {
            window.addEventListener(evt, function() {
                lastActivityTime = Date.now();
            }, { passive: true });
        });

        // Mouse Move for Eye Tracking
        window.addEventListener('mousemove', updateEyeTracking, { passive: true });

        // Mouse Leave Viewport
        document.addEventListener('mouseleave', function() {
            isMouseInViewport = false;
            targetEyeX = 0;
            targetEyeY = 0;
            targetHeadAngle = 0;
        });

        // Hover Handlers on Mascot
        launcherBtn.addEventListener('mouseenter', function() {
            isMascotHovered = true;
            lastActivityTime = Date.now();
            launcherBtn.classList.add('riva-arm-raised');
            if (speechBubble) {
                var textEl = speechBubble.querySelector('.rdwis-bubble-text');
                if (textEl) textEl.textContent = "Hi, I'm RIVA! How may I help you today?";
            }
            if (!isChatOpen) {
                showSpeechBubble(false);
            }
        });

        launcherBtn.addEventListener('mouseleave', function() {
            isMascotHovered = false;
            launcherBtn.classList.remove('riva-arm-raised');
            if (speechBubble && !isChatOpen) {
                speechBubbleTimer = setTimeout(hideSpeechBubble, 2500);
            }
        });

        // Speech Bubble Click opens Chat
        if (speechBubble) {
            speechBubble.addEventListener('click', function(e) {
                e.stopPropagation();
                if (launcherBtn) launcherBtn.click();
            });
        }

        // Initial Greeting Wave & Speech Bubble (Once per session)
        var hasGreeted = sessionStorage.getItem('rdwis_riva_greeted');
        if (!hasGreeted) {
            setTimeout(function() {
                triggerGreetingWave();
                setTimeout(function() {
                    showSpeechBubble(true);
                    sessionStorage.setItem('rdwis_riva_greeted', 'true');
                }, 900);
            }, 800);
        }

        // Start Natural Blinking & Idle Checkers
        scheduleNextBlink();
        startIdleTracker();

        // Monitor PWA banner updates
        setInterval(checkPwaBannerCollision, 2000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
