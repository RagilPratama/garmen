let Swal = null;

try {
    const swalModule = await import('sweetalert2');
    Swal = swalModule.default;
} catch (e) {
    console.warn('SweetAlert2 not available, using native alerts');
}

export const useAlert = () => {
    const fire = async (options) => {
        if (Swal) {
            return await Swal.fire(options);
        }
        const { icon, title, text, html, showCancelButton, confirmButtonText, cancelButtonText } = options;

        const message = html || text || title;

        if (showCancelButton) {
            const result = confirm(message);
            return { isConfirmed: result, isDismissed: !result };
        } else {
            alert(message);
            return { isConfirmed: true };
        }
    };

    const showLoading = () => {
        if (Swal) {
            Swal.showLoading();
        }
    };

    const close = () => {
        if (Swal) {
            Swal.close();
        }
    };

    return {
        fire,
        showLoading,
        close,
    };
};
