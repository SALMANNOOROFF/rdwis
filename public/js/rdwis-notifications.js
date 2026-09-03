/**
 * ============================================================
 *  RDWIS NOTIFICATION SYSTEM
 *  - Real-time Polling (Every 25s)
 *  - Enhanced Dropdown Window with Warm Neutral Design
 *  - Interactive Top-of-Screen Slide-in Popup Banner
 *  - Mark All as Read Handler
 * ============================================================
 */
$(function() {
    const PNT_INTERVAL = 25000; // 25 seconds
    const badge = $('#pnt-count');
    const list = $('#pnt-list');
    const headerBadge = $('#pnt-badge-header');

    // Track known notification IDs to detect NEW incoming items
    let seenNotifIds = new Set();
    try {
        const stored = sessionStorage.getItem('rdwis_seen_notifs');
        if (stored) {
            JSON.parse(stored).forEach(id => seenNotifIds.add(id));
        }
    } catch (e) {}

    // Subtle audio chime for new notifications
    function playNotificationChime() {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            const now = ctx.currentTime;
            
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(587.33, now); // D5
            osc.frequency.exponentialRampToValueAtTime(880, now + 0.15); // A5
            gain.gain.setValueAtTime(0.12, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start(now);
            osc.stop(now + 0.35);
        } catch (e) {
            // Audio context not allowed without prior user interaction, fail silently
        }
    }

    // Helper: format relative time
    function formatTimeAgo(dateString) {
        if (!dateString) return 'Recent';
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Recent';
        
        const seconds = Math.floor((new Date() - date) / 1000);
        if (seconds < 60) return 'Just now';
        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) return `${minutes}m ago`;
        const hours = Math.floor(minutes / 60);
        if (hours < 24) return `${hours}h ago`;
        const days = Math.floor(hours / 24);
        return `${days}d ago`;
    }

    // Helper: escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Trigger Top-of-Screen Slide-in Banner
    function showTopScreenPopup(notif) {
        // Remove existing banner if any
        $('#rdwis-top-notif-toast').remove();

        const message = escapeHtml(notif.pnt_message || 'New update received');
        const link = `/nrdi/purchase-cases-new/${notif.pnt_pcs_id}`;

        const toastHtml = `
            <div id="rdwis-top-notif-toast" class="rdwis-top-toast" style="
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%) translateY(-120%);
                z-index: 100000;
                width: 90%;
                max-width: 480px;
                background: #FFFFFF;
                background: rgba(255, 255, 255, 0.96);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1.5px solid #CBD8C5;
                border-left: 5px solid #5F7858;
                border-radius: 12px;
                padding: 12px 16px;
                box-shadow: 0 12px 35px rgba(41, 40, 36, 0.18), 0 4px 12px rgba(0,0,0,0.06);
                display: flex;
                align-items: center;
                gap: 12px;
                transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
                opacity: 0;
            ">
                <div style="
                    width: 38px;
                    height: 38px;
                    border-radius: 50%;
                    background: #F1F4EE;
                    color: #5F7858;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                    font-size: 15px;
                ">
                    <i class="fas fa-bell notif-bell-ring"></i>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 11px; font-weight: 700; color: #5F7858; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 2px;">
                        New Case Update
                    </div>
                    <div style="font-size: 13px; font-weight: 600; color: #292824; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2;">
                        ${message}
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
                    <a href="${link}" class="btn btn-sm" style="
                        background: #5F7858;
                        color: #FFFFFF;
                        font-size: 11.5px;
                        font-weight: 600;
                        border-radius: 20px;
                        padding: 4px 12px;
                        box-shadow: 0 2px 6px rgba(95, 120, 88, 0.3);
                        text-decoration: none;
                    ">View</a>
                    <button type="button" class="btn btn-sm btn-link p-0 text-muted" onclick="$('#rdwis-top-notif-toast').css({'transform': 'translateX(-50%) translateY(-120%)', 'opacity': 0}); setTimeout(() => $('#rdwis-top-notif-toast').remove(), 400);" style="font-size: 16px; line-height: 1; color: #77736B;">
                        &times;
                    </button>
                </div>
            </div>
        `;

        $('body').append(toastHtml);

        // Slide down
        setTimeout(() => {
            $('#rdwis-top-notif-toast').css({
                'transform': 'translateX(-50%) translateY(0)',
                'opacity': '1'
            });
            playNotificationChime();
        }, 50);

        // Auto dismiss after 7 seconds
        setTimeout(() => {
            const toast = $('#rdwis-top-notif-toast');
            if (toast.length) {
                toast.css({
                    'transform': 'translateX(-50%) translateY(-120%)',
                    'opacity': '0'
                });
                setTimeout(() => toast.remove(), 400);
            }
        }, 7000);
    }

    function fetchNotifications() {
        $.get('/notifications/unread', function(data) {
            const count = data.count || 0;
            
            if (count > 0) {
                badge.text(count).removeClass('d-none');
                headerBadge.text(`${count} New`).removeClass('d-none');
            } else {
                badge.addClass('d-none');
                headerBadge.text('0 New');
            }

            if (data.notifications && data.notifications.length > 0) {
                let html = '';
                let hasBrandNew = false;
                let newestNotif = null;

                data.notifications.forEach(n => {
                    const timeAgo = formatTimeAgo(n.created_at);
                    const safeMsg = escapeHtml(n.pnt_message);
                    const notifId = n.pnt_id || `${n.pnt_pcs_id}_${n.created_at}`;

                    // Check if newly arrived in this session
                    if (!seenNotifIds.has(notifId)) {
                        seenNotifIds.add(notifId);
                        hasBrandNew = true;
                        if (!newestNotif) newestNotif = n;
                    }

                    html += `
                        <a href="/nrdi/purchase-cases-new/${n.pnt_pcs_id}" class="dropdown-item notif-item-enhanced d-flex align-items-start py-2 px-3 border-bottom text-decoration-none" style="gap: 12px; transition: background 0.15s ease;">
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-1" style="width: 32px; height: 32px; background: var(--rd-primary-50); color: var(--rd-primary-700); border: 1px solid var(--rd-primary-200);">
                                <i class="fas fa-file-invoice" style="font-size: 13px;"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0" style="overflow: hidden;">
                                <div class="font-weight-600 text-truncate" style="font-size: 12.5px; color: var(--rd-text1); line-height: 1.3;" title="${safeMsg}">
                                    ${safeMsg}
                                </div>
                                <div class="text-xs mt-1 d-flex align-items-center" style="font-size: 11px; color: var(--rd-text3); gap: 4px;">
                                    <i class="far fa-clock" style="font-size: 10px;"></i>
                                    <span>${timeAgo}</span>
                                </div>
                            </div>
                            <span class="rounded-circle mt-2 flex-shrink-0" style="width: 7px; height: 7px; background: var(--rd-primary-600);"></span>
                        </a>
                    `;
                });

                list.html(html);

                // Save seen IDs to sessionStorage
                try {
                    sessionStorage.setItem('rdwis_seen_notifs', JSON.stringify(Array.from(seenNotifIds)));
                } catch (e) {}

                // Trigger top screen pop-up if a brand new notification arrived
                if (hasBrandNew && newestNotif) {
                    showTopScreenPopup(newestNotif);
                }

            } else {
                list.html(`
                    <div class="p-4 text-center text-muted" style="font-size: 13px;">
                        <i class="far fa-bell-slash fa-2x mb-2 d-block text-muted opacity-50"></i>
                        No new notifications
                    </div>
                `);
            }

            // Real-time Sidebar Badges (Parent to Child Blinking Badges)
            if (data && data.badges) {
                updateSidebarBadges(data.badges);
            }
        });
    }

    function updateSidebarBadges(badges) {
        if (!badges) return;

        // Purchase Cases Badge
        const purCount = parseInt(badges.purchase_cases || 0, 10);
        if (purCount > 0) {
            $('.badge-pur-parent').text(purCount).removeClass('d-none');
            $('.badge-pur-child').text(purCount).removeClass('d-none');
        } else {
            $('.badge-pur-parent').addClass('d-none');
            $('.badge-pur-child').addClass('d-none');
        }

        // Contract Cases Badge
        const ctrCount = parseInt(badges.contract_cases || 0, 10);
        if (ctrCount > 0) {
            $('.badge-ctr-parent').text(ctrCount).removeClass('d-none');
            $('.badge-ctr-child').text(ctrCount).removeClass('d-none');
        } else {
            $('.badge-ctr-parent').addClass('d-none');
            $('.badge-ctr-child').addClass('d-none');
        }

        // HR & Hired Employees Badge (Expiring Contracts)
        const hrCount = parseInt(badges.hr || badges.hired_emps || 0, 10);
        if (hrCount > 0) {
            $('.badge-hr-parent').text(hrCount).removeClass('d-none');
            $('.badge-hr-child').text(hrCount).removeClass('d-none');
        } else {
            $('.badge-hr-parent').addClass('d-none');
            $('.badge-hr-child').addClass('d-none');
        }
    }

    // Mark All As Read
    $('#pnt-mark-all').on('click', function(e) {
        e.preventDefault();
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        
        $.post('/notifications/mark-all-read', { _token: csrfToken }, function() {
            badge.addClass('d-none');
            headerBadge.text('0 New');
            list.html(`
                <div class="p-4 text-center text-muted" style="font-size: 13px;">
                    <i class="far fa-bell-slash fa-2x mb-2 d-block text-muted opacity-50"></i>
                    No new notifications
                </div>
            `);
            if (typeof Swal !== 'undefined') {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: false
                });
                Toast.fire({
                    icon: 'success',
                    title: 'All notifications marked as read'
                });
            }
        });
    });

    // Initial fetch on page load
    fetchNotifications();

    // Regular polling interval
    setInterval(fetchNotifications, PNT_INTERVAL);
});
