export function useToast() {
    const success = (message, title = 'Success') => {
        if (window.toast) {
            window.toast.fire({ icon: 'success', title: message });
        } else if (window.Swal) {
            window.Swal.fire({ icon: 'success', title, text: message, timer: 3000 });
        } else {
            alert(`${title}: ${message}`);
        }
    };

    const error = (message, title = 'Error') => {
        if (window.toast) {
            window.toast.fire({ icon: 'error', title: message });
        } else if (window.Swal) {
            window.Swal.fire({ icon: 'error', title, text: message });
        } else {
            alert(`${title}: ${message}`);
        }
    };

    const info = (message, title = 'Information') => {
        if (window.toast) {
            window.toast.fire({ icon: 'info', title: message });
        } else if (window.Swal) {
            window.Swal.fire({ icon: 'info', title, text: message, timer: 3000 });
        } else {
            alert(`${title}: ${message}`);
        }
    };

    const confirm = async ({ title = 'Are you sure?', text = '', confirmButtonText = 'Yes, proceed', cancelButtonText = 'Cancel' } = {}) => {
        if (window.Swal) {
            const res = await window.Swal.fire({
                title,
                text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#800000', // Maroon brand color
                cancelButtonColor: '#6c757d',
                confirmButtonText,
                cancelButtonText,
            });
            return res.isConfirmed;
        }
        return window.confirm(text || title);
    };

    return {
        success,
        error,
        info,
        confirm,
    };
}
