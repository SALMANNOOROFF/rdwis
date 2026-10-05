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

    // Draggable Mascot & 2-Minute Auto Return State
    var AUTO_RETURN_DELAY = 120000; // 2 minutes (120 seconds in milliseconds)
    var autoReturnTimer = null;
    var isDragging = false;
    var isPointerDown = false;
    var wasMovedAway = false;
    var dragStartX = 0;
    var dragStartY = 0;
    var dragStartLeft = 0;
    var dragStartTop = 0;
    var dragThreshold = 5;

    // Target and Current Eye Tracking Coordinates
    var targetEyeX = 0;
    var targetEyeY = 0;
    var currentEyeX = 0;
    var currentEyeY = 0;
    var targetHeadAngle = 0;
    var currentHeadAngle = 0;
    var isTrackingRafActive = false;

    // DOM References (Cached once DOM is ready)
    var container = null;
    var launcherBtn = null;
    var speechBubble = null;
    var headRig = null;
    var eyeLeft = null;
    var eyeRight = null;
    var armLeft = null;
    var mouthRig = null;
    var floatRig = null;
    var pwaBanner = null;
    var chatWindow = null;
    var chatHeader = null;

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
        container = document.getElementById('rdwisAiWidgetContainer');
        launcherBtn = document.getElementById('rdwisRobotLauncher') || document.getElementById('rdwisAiToggleBtn');
        speechBubble = document.getElementById('rdwisSpeechBubble');
        headRig = document.getElementById('robot-head');
        eyeLeft = document.getElementById('eye-left');
        eyeRight = document.getElementById('eye-right');
        armLeft = document.getElementById('robot-arm-left');
        mouthRig = document.getElementById('robot-mouth');
        floatRig = document.getElementById('robot-float-rig');
        pwaBanner = document.getElementById('pwa-install-banner');
        chatWindow = document.getElementById('rdwisAiChatWindow');
        chatHeader = document.querySelector('.rdwis-ai-header');

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
     * DRAGGABLE MASCOT & 2-MINUTE AUTO RETURN CONTROLLER
     */

    function handleDragStart(e) {
        // Accept primary button (left mouse) or touch
        if (e.button !== undefined && e.button !== 0) return;

        // Ignore interactive action elements inside chat window
        if (e.target.closest('#rdwisAiCloseBtn, #rdwisAiClearBtn, button:not(#rdwisAiToggleBtn):not(#rdwisRobotLauncher), input, textarea, a')) {
            return;
        }

        if (!container) return;

        // Clear active return countdown while user is holding or repositioning
        if (autoReturnTimer) {
            clearTimeout(autoReturnTimer);
            autoReturnTimer = null;
        }

        var zoom = getBodyZoomFactor();
        var rect = container.getBoundingClientRect();

        isPointerDown = true;
        isDragging = false;
        dragStartX = e.clientX;
        dragStartY = e.clientY;
        dragStartLeft = rect.left / zoom;
        dragStartTop = rect.top / zoom;

        window.addEventListener('pointermove', handleDragMove, { passive: false });
        window.addEventListener('pointerup', handleDragEnd, { passive: false });
        window.addEventListener('pointercancel', handleDragEnd, { passive: false });
    }

    function handleDragMove(e) {
        if (!isPointerDown || !container) return;

        var deltaX = e.clientX - dragStartX;
        var deltaY = e.clientY - dragStartY;
        var dist = Math.hypot(deltaX, deltaY);

        if (!isDragging && dist > dragThreshold) {
            isDragging = true;
            window.__RIVA_WAS_DRAGGED__ = true;
            container.classList.add('is-dragging');

            // Switch from stylesheet bottom/right to explicit screen coordinates
            container.style.transition = 'none';
            container.style.right = 'auto';
            container.style.bottom = 'auto';
        }

        if (isDragging) {
            if (e.cancelable) e.preventDefault();

            var zoom = getBodyZoomFactor();
            var newLeft = dragStartLeft + (deltaX / zoom);
            var newTop = dragStartTop + (deltaY / zoom);

            // Clamp within viewport
            var vw = window.innerWidth / zoom;
            var vh = window.innerHeight / zoom;
            var w = container.offsetWidth || 100;
            var h = container.offsetHeight || 120;

            var minLeft = 10;
            var maxLeft = Math.max(10, vw - w - 10);
            var minTop = 10;
            var maxTop = Math.max(10, vh - h - 10);

            newLeft = Math.max(minLeft, Math.min(newLeft, maxLeft));
            newTop = Math.max(minTop, Math.min(newTop, maxTop));

            container.style.left = Math.round(newLeft) + 'px';
            container.style.top = Math.round(newTop) + 'px';

            updateAdaptiveOrientation(newLeft, newTop);
        }
    }

    function updateAdaptiveOrientation(left, top) {
        if (!chatWindow) chatWindow = document.getElementById('rdwisAiChatWindow');
        if (chatWindow) {
            if (top < 380) {
                chatWindow.classList.add('open-downwards');
            } else {
                chatWindow.classList.remove('open-downwards');
            }

            if (left < 380) {
                chatWindow.classList.add('open-rightwards');
            } else {
                chatWindow.classList.remove('open-rightwards');
            }
        }

        if (speechBubble) {
            if (top < 130) {
                speechBubble.classList.add('bubble-downwards');
            } else {
                speechBubble.classList.remove('bubble-downwards');
            }
        }
    }

    function handleDragEnd() {
        if (!isPointerDown) return;
        isPointerDown = false;

        window.removeEventListener('pointermove', handleDragMove);
        window.removeEventListener('pointerup', handleDragEnd);
        window.removeEventListener('pointercancel', handleDragEnd);

        if (container) {
            container.classList.remove('is-dragging');
        }

        if (isDragging) {
            isDragging = false;

            // Keep flag active for 250ms so following click event is safely suppressed
            setTimeout(function() {
                window.__RIVA_WAS_DRAGGED__ = false;
            }, 250);

            // Check if dropped away from default dock position
            if (isAwayFromHome()) {
                wasMovedAway = true;
                scheduleAutoReturn(AUTO_RETURN_DELAY);
            } else {
                restoreHomeDock();
            }
        } else {
            window.__RIVA_WAS_DRAGGED__ = false;
        }
    }

    function isAwayFromHome() {
        if (!container) return false;
        var zoom = getBodyZoomFactor();
        var rect = container.getBoundingClientRect();
        var vw = window.innerWidth / zoom;
        var vh = window.innerHeight / zoom;
        var isPwa = document.body.classList.contains('has-pwa-banner');
        var homeBottom = isPwa ? 96 : 24;
        var homeRight = 28;

        var expectedLeft = vw - (container.offsetWidth || 100) - homeRight;
        var expectedTop = vh - (container.offsetHeight || 120) - homeBottom;

        var curLeft = rect.left / zoom;
        var curTop = rect.top / zoom;

        var dist = Math.hypot(curLeft - expectedLeft, curTop - expectedTop);
        return dist > 40;
    }

    function scheduleAutoReturn(delayMs) {
        if (autoReturnTimer) {
            clearTimeout(autoReturnTimer);
            autoReturnTimer = null;
        }

        var delay = (typeof delayMs === 'number') ? delayMs : AUTO_RETURN_DELAY;
        autoReturnTimer = setTimeout(function() {
            returnToHome();
        }, delay);
    }

    function returnToHome() {
        if (!container || isDragging || isPointerDown) return;

        if (!isAwayFromHome()) {
            restoreHomeDock();
            return;
        }

        var zoom = getBodyZoomFactor();
        var vw = window.innerWidth / zoom;
        var vh = window.innerHeight / zoom;
        var isPwa = document.body.classList.contains('has-pwa-banner');
        var homeBottom = isPwa ? 96 : 24;
        var homeRight = 28;

        var targetLeft = vw - (container.offsetWidth || 100) - homeRight;
        var targetTop = vh - (container.offsetHeight || 120) - homeBottom;

        container.classList.add('riva-flying-home');
        container.style.transition = 'left 1.05s cubic-bezier(0.34, 1.25, 0.64, 1), top 1.05s cubic-bezier(0.34, 1.25, 0.64, 1)';
        container.style.left = Math.round(targetLeft) + 'px';
        container.style.top = Math.round(targetTop) + 'px';

        setTimeout(function() {
            restoreHomeDock();
            if (container) {
                container.classList.remove('riva-flying-home');
            }

            // Friendly mascot wave & speech bubble on reaching home station
            triggerGreetingWave();
            if (window.RivaRobot && window.RivaRobot.showBubble) {
                window.RivaRobot.showBubble("Back at my station! 🤖");
            }
        }, 1100);
    }

    function restoreHomeDock() {
        if (!container) return;
        if (autoReturnTimer) {
            clearTimeout(autoReturnTimer);
            autoReturnTimer = null;
        }
        wasMovedAway = false;
        container.style.transition = '';
        container.style.left = '';
        container.style.top = '';
        container.style.right = '';
        container.style.bottom = '';

        if (chatWindow) {
            chatWindow.classList.remove('open-downwards', 'open-rightwards');
        }
        if (speechBubble) {
            speechBubble.classList.remove('bubble-downwards');
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
            } else if (wasMovedAway) {
                // When closing chat while away, restart the 2-minute countdown
                scheduleAutoReturn(AUTO_RETURN_DELAY);
            }
        },

        wave: function() {
            triggerGreetingWave();
        },

        returnToHome: function() {
            returnToHome();
        },

        resetReturnTimer: function() {
            if (wasMovedAway) {
                scheduleAutoReturn(AUTO_RETURN_DELAY);
            }
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

        // Pointer Down for Mascot Dragging
        if (launcherBtn) {
            launcherBtn.addEventListener('pointerdown', handleDragStart);
        }
        if (speechBubble) {
            speechBubble.addEventListener('pointerdown', handleDragStart);
        }
        if (chatHeader) {
            chatHeader.addEventListener('pointerdown', handleDragStart);
        }

        // Viewport resize keeps mascot safely within bounds
        window.addEventListener('resize', function() {
            if (wasMovedAway && container && !isDragging) {
                var zoom = getBodyZoomFactor();
                var rect = container.getBoundingClientRect();
                var vw = window.innerWidth / zoom;
                var vh = window.innerHeight / zoom;
                var curLeft = rect.left / zoom;
                var curTop = rect.top / zoom;

                var maxLeft = Math.max(10, vw - (container.offsetWidth || 100) - 10);
                var maxTop = Math.max(10, vh - (container.offsetHeight || 120) - 10);

                var clampedLeft = Math.max(10, Math.min(curLeft, maxLeft));
                var clampedTop = Math.max(10, Math.min(curTop, maxTop));

                container.style.left = Math.round(clampedLeft) + 'px';
                container.style.top = Math.round(clampedTop) + 'px';
                updateAdaptiveOrientation(clampedLeft, clampedTop);
            }
        });

        // Speech Bubble Click opens Chat (only if not dragged)
        if (speechBubble) {
            speechBubble.addEventListener('click', function(e) {
                if (window.__RIVA_WAS_DRAGGED__) return;
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
