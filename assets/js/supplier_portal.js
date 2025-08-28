document.addEventListener('DOMContentLoaded', () => {
    const notificationButton = document.getElementById('notification-button');
    const notificationPanel = document.getElementById('notification-panel');

    if (notificationButton && notificationPanel) {
        notificationButton.addEventListener('click', (event) => {
            event.stopPropagation();
            const isHidden = notificationPanel.style.display === 'none' || notificationPanel.style.display === '';
            
            notificationPanel.style.display = isHidden ? 'block' : 'none';

            if (isHidden && notificationButton.querySelector('.notification-count')) {
                fetch('?mark_notifications_as_read=true')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Remove the count badge
                            const countBadge = notificationButton.querySelector('.notification-count');
                            if (countBadge) {
                                countBadge.remove();
                            }
                            // Visually mark all items as read
                            const unreadItems = notificationPanel.querySelectorAll('.notification-item[data-read="0"]');
                            unreadItems.forEach(item => {
                                item.classList.remove('unread');
                                const dot = item.querySelector('.unread-dot');
                                if (dot) {
                                    dot.remove();
                                }
                                item.setAttribute('data-read', '1');
                            });
                        }
                    })
                    .catch(error => console.error('Error marking notifications as read:', error));
            }
        });

        window.addEventListener('click', (event) => {
            if (notificationPanel.style.display === 'block' && !notificationPanel.contains(event.target)) {
                notificationPanel.style.display = 'none';
            }
        });
    }
});