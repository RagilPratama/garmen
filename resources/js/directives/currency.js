/**
 * Currency directive for formatting number inputs with thousand separators
 * Usage: v-currency on input elements
 */

const formatNumber = (value) => {
    if (!value && value !== 0) return "";
    const num = String(value).replace(/\D/g, "");
    if (!num) return "";
    return new Intl.NumberFormat("id-ID").format(parseInt(num));
};

const parseNumber = (value) => {
    if (!value) return 0;
    return parseInt(String(value).replace(/\D/g, "")) || 0;
};

export default {
    mounted(el, binding) {
        const input = el.tagName === "INPUT" ? el : el.querySelector("input");
        if (!input) return;

        // Format initial value
        if (input.value) {
            input.value = formatNumber(input.value);
        }

        // Handle input event
        const handleInput = (e) => {
            const cursorPos = e.target.selectionStart;
            const oldValue = e.target.value;
            const oldLength = oldValue.length;

            // Get numeric value
            const numericValue = parseNumber(e.target.value);

            // Format and set value
            const formattedValue = formatNumber(numericValue);
            e.target.value = formattedValue;

            // Restore cursor position
            const newLength = formattedValue.length;
            const diff = newLength - oldLength;
            const newCursorPos = cursorPos + diff;
            e.target.setSelectionRange(newCursorPos, newCursorPos);

            // Emit the numeric value to v-model
            if (binding.instance && binding.value) {
                const modelValue = binding.value;
                if (
                    typeof modelValue === "object" &&
                    modelValue.value !== undefined
                ) {
                    modelValue.value = numericValue;
                }
            }

            // Trigger input event for v-model
            e.target.dispatchEvent(new Event("input", { bubbles: true }));
        };

        // Handle blur to ensure formatting
        const handleBlur = (e) => {
            const numericValue = parseNumber(e.target.value);
            e.target.value = formatNumber(numericValue);
        };

        input.addEventListener("input", handleInput);
        input.addEventListener("blur", handleBlur);

        // Store handlers for cleanup
        input._currencyHandlers = { handleInput, handleBlur };
    },

    updated(el, binding) {
        const input = el.tagName === "INPUT" ? el : el.querySelector("input");
        if (!input) return;

        // Update formatted value if model changes externally
        if (binding.value !== binding.oldValue) {
            const numericValue =
                typeof binding.value === "object" &&
                binding.value.value !== undefined
                    ? binding.value.value
                    : binding.value;

            if (document.activeElement !== input) {
                input.value = formatNumber(numericValue);
            }
        }
    },

    unmounted(el) {
        const input = el.tagName === "INPUT" ? el : el.querySelector("input");
        if (!input || !input._currencyHandlers) return;

        input.removeEventListener("input", input._currencyHandlers.handleInput);
        input.removeEventListener("blur", input._currencyHandlers.handleBlur);
        delete input._currencyHandlers;
    },
};
