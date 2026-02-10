document.addEventListener('DOMContentLoaded', function () {
    const bellIcon = document.getElementById('notification-bell');
    const badge = document.getElementById('notification-count');
    const dropdown = document.getElementById('notification-dropdown');
    const list = document.getElementById('notification-list');

    // Toggle Dropdown
    bellIcon.addEventListener('click', function (e) {
        e.preventDefault();
        dropdown.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!bellIcon.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    // Fetch Notifications
    function fetchNotifications() {
        fetch('api/get_notifications.php')
            .then(response => response.json())
            .then(data => {
                if (data.error) return;

                // Update Badge
                if (data.unread_count > 0) {
                    badge.style.display = 'inline-block';
                    badge.innerText = data.unread_count;
                } else {
                    badge.style.display = 'none';
                }

                // Update List
                list.innerHTML = '';
                if (data.notifications.length === 0) {
                    list.innerHTML = '<li style="padding: 10px; color: #777;">No notifications</li>';
                } else {
                    data.notifications.forEach(notif => {
                        const li = document.createElement('li');
                        li.className = notif.is_read == 1 ? 'read' : 'unread';
                        li.innerHTML = `
                            <div class="message">${notif.message}</div>
                            <small>${new Date(notif.created_at).toLocaleString()}</small>
                        `;
                        li.addEventListener('click', () => markAsRead(notif.id, li));
                        list.appendChild(li);
                    });
                }
            })
            .catch(err => console.error('Error fetching notifications:', err));
    }

    // Mark as Read
    function markAsRead(id, element) {
        fetch('api/mark_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    element.className = 'read';
                    fetchNotifications(); // Refresh count
                }
            });
    }

    // Initial Fetch & Poll
    fetchNotifications();
    setInterval(fetchNotifications, 30000); // Poll every 30s
});
