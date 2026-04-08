$(function() {
    const PNT_INTERVAL = 30000; // 30 seconds
    const bell = $('#pnt-bell');
    const badge = $('#pnt-count');
    const list = $('#pnt-list');

    function fetchNotifications() {
        $.get('/notifications/unread', function(data) {
            if (data.count > 0) {
                badge.text(data.count).removeClass('d-none');
            } else {
                badge.addClass('d-none');
            }

            if (data.notifications.length > 0) {
                let html = '';
                data.notifications.forEach(n => {
                    html += `
                        <a href="/approvals/show/${n.pnt_pcs_id}" class="dropdown-item">
                            <i class="fas fa-file-invoice mr-2"></i> ${n.pnt_message.substring(0, 40)}...
                        </a>
                        <div class="dropdown-divider"></div>
                    `;
                });
                list.html(html);
            } else {
                list.html('<div class="dropdown-item text-center text-muted">No new notifications</div>');
            }
        });
    }

    $('#pnt-mark-all').on('click', function(e) {
        e.preventDefault();
        $.post('/notifications/mark-all-read', { _token: $('meta[name="csrf-token"]').attr('content') }, function() {
            badge.addClass('d-none');
            list.html('<div class="dropdown-item text-center text-muted">No new notifications</div>');
        });
    });

    // Initial fetch
    fetchNotifications();
    // Poll every 30s
    setInterval(fetchNotifications, PNT_INTERVAL);
});
