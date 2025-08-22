/**
 * Custom Toast Notification Component
 * Clean toast implementation with theme support
 */
class CustomAlert {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Create container for toasts if it doesn't exist
        if (!document.getElementById('custom-alerts-container')) {
            this.container = document.createElement('div');
            this.container.id = 'custom-alerts-container';
            this.container.style.cssText = `
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 1050;
                max-width: 500px;
                width: 100%;
                pointer-events: none;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 12px;
            `;
            document.body.appendChild(this.container);
        } else {
            this.container = document.getElementById('custom-alerts-container');
        }
    }

    /**
     * Show a toast with clean styling
     * @param {string} message - Toast message
     * @param {string} severity - Toast severity: 'success', 'info', 'warning', 'error'
     * @param {number} duration - Duration in milliseconds (default: 4000)
     * @param {string} title - Optional title for the toast
     */
    show(message, severity = 'info', duration = 4000, title = null) {
        const toastElement = this.createToastElement(message, severity, title);
        this.container.appendChild(toastElement);

        // Trigger animation
        setTimeout(() => {
            toastElement.style.transform = 'translateY(0)';
            toastElement.style.opacity = '1';
        }, 10);

        // Auto remove after duration
        if (duration > 0) {
            setTimeout(() => {
                this.removeAlert(toastElement);
            }, duration);
        }

        return toastElement;
    }

    createToastElement(message, severity, title = null) {
        const toast = document.createElement('div');
        
        // Check if dark mode is active - fix: check documentElement instead of body
        const isDarkMode = document.documentElement.classList.contains('dark-mode');
        
        // Clean color scheme based on theme - white background for both modes
        const colors = {
            success: {
                bg: isDarkMode ? '#2b2b2b' : '#ffffff',
                text: isDarkMode ? '#ffffff' : '#333333',
                titleText: isDarkMode ? '#ffffff' : '#1a1a1a',
                iconColor: '#10b981', // Green for success
                border: isDarkMode ? '#374151' : '#e5e7eb',
                title: title || 'Success',
                icon: '✓'
            },
            info: {
                bg: isDarkMode ? '#2b2b2b' : '#ffffff',
                text: isDarkMode ? '#ffffff' : '#333333',
                titleText: isDarkMode ? '#ffffff' : '#1a1a1a',
                iconColor: '#3b82f6', // Blue for info
                border: isDarkMode ? '#374151' : '#e5e7eb',
                title: title || 'Information',
                icon: 'ℹ'
            },
            warning: {
                bg: isDarkMode ? '#2b2b2b' : '#ffffff',
                text: isDarkMode ? '#ffffff' : '#333333',
                titleText: isDarkMode ? '#ffffff' : '#1a1a1a',
                iconColor: '#f59e0b', // Amber for warning
                border: isDarkMode ? '#374151' : '#e5e7eb',
                title: title || 'Warning',
                icon: '!'
            },
            error: {
                bg: isDarkMode ? '#2b2b2b' : '#ffffff',
                text: isDarkMode ? '#ffffff' : '#333333',
                titleText: isDarkMode ? '#ffffff' : '#1a1a1a',
                iconColor: '#ef4444', // Red for error
                border: isDarkMode ? '#374151' : '#e5e7eb',
                title: title || 'Error',
                icon: '×'
            }
        };

        const config = colors[severity] || colors.info;

        toast.className = 'mui-custom-toast';
        toast.style.cssText = `
            display: flex;
            align-items: center;
            gap: 12px;
            background-color: ${config.bg};
            color: ${config.text};
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, ${isDarkMode ? '0.3' : '0.1'}), 0 2px 8px rgba(0, 0, 0, ${isDarkMode ? '0.2' : '0.05'});
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 18px;
            position: relative;
            pointer-events: auto;
            transform: translateY(-20px);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
            border: 1px solid ${config.border};
            min-width: 320px;
            max-width: 480px;
            width: 100%;
        `;

        // Create icon container
        const iconContainer = document.createElement('div');
        iconContainer.style.cssText = `
            flex-shrink: 0;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background-color: ${config.iconColor};
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
        `;
        iconContainer.textContent = config.icon;

        // Create content container
        const contentContainer = document.createElement('div');
        contentContainer.style.cssText = `
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 4px;
        `;

        // Create title
        const titleElement = document.createElement('div');
        titleElement.style.cssText = `
            font-weight: 600;
            font-size: 16px;
            color: ${config.titleText};
            line-height: 1.2;
        `;
        titleElement.textContent = config.title;

        // Create message
        const messageElement = document.createElement('div');
        messageElement.style.cssText = `
            color: ${config.text};
            line-height: 1.4;
            font-size: 14px;
            opacity: 0.9;
        `;
        // Support HTML content in messages
        if (message.includes('<') && message.includes('>')) {
            messageElement.innerHTML = message;
        } else {
            messageElement.textContent = message;
        }

        // Create close button
        const closeBtn = document.createElement('button');
        closeBtn.style.cssText = `
            background: none;
            border: none;
            color: ${config.text};
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            opacity: 0.5;
            transition: opacity 0.2s ease, background-color 0.2s ease;
            font-size: 18px;
            font-weight: bold;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        `;
        closeBtn.innerHTML = '×';
        closeBtn.onclick = () => this.removeAlert(toast);
        
        closeBtn.onmouseover = () => {
            closeBtn.style.opacity = '1';
            closeBtn.style.backgroundColor = isDarkMode ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';
        };
        closeBtn.onmouseout = () => {
            closeBtn.style.opacity = '0.5';
            closeBtn.style.backgroundColor = 'transparent';
        };

        // Assemble the toast
        contentContainer.appendChild(titleElement);
        contentContainer.appendChild(messageElement);
        
        toast.appendChild(iconContainer);
        toast.appendChild(contentContainer);
        toast.appendChild(closeBtn);

        return toast;
    }

    removeAlert(toastElement) {
        toastElement.style.transform = 'translateY(-20px)';
        toastElement.style.opacity = '0';
        
        setTimeout(() => {
            if (toastElement.parentNode) {
                toastElement.parentNode.removeChild(toastElement);
            }
        }, 300);
    }

    // Convenience methods
    success(message, duration = 4000, title = null) {
        return this.show(message, 'success', duration, title);
    }

    info(message, duration = 4000, title = null) {
        return this.show(message, 'info', duration, title);
    }

    warning(message, duration = 4000, title = null) {
        return this.show(message, 'warning', duration, title);
    }

    error(message, duration = 4000, title = null) {
        return this.show(message, 'error', duration, title);
    }
}

// Global instance
window.customAlert = new CustomAlert();

// Export for module use if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CustomAlert;
}
