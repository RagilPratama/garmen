import { ref, watch } from 'vue'

/**
 * Composable for handling currency input formatting
 * @param {Ref<number>} modelValue - The numeric value ref
 * @returns {Object} - Object with displayValue ref and helper functions
 */
export function useCurrencyInput(modelValue) {
  const displayValue = ref('')

  const formatNumber = (value) => {
    if (!value && value !== 0) return ''
    const num = String(value).replace(/\D/g, '')
    if (!num) return ''
    return new Intl.NumberFormat('id-ID').format(parseInt(num))
  }

  const parseNumber = (value) => {
    if (!value) return 0
    return parseInt(String(value).replace(/\D/g, '')) || 0
  }

  // Initialize display value
  displayValue.value = formatNumber(modelValue.value)

  // Watch for external changes to model value
  watch(modelValue, (newVal) => {
    displayValue.value = formatNumber(newVal)
  })

  const handleInput = (event) => {
    const input = event.target
    const cursorPos = input.selectionStart
    const oldValue = input.value
    const oldLength = oldValue.length

    // Get numeric value
    const numericValue = parseNumber(input.value)
    
    // Update model
    modelValue.value = numericValue
    
    // Format display
    const formattedValue = formatNumber(numericValue)
    displayValue.value = formattedValue
    input.value = formattedValue

    // Restore cursor position
    const newLength = formattedValue.length
    const diff = newLength - oldLength
    const newCursorPos = cursorPos + diff
    input.setSelectionRange(newCursorPos, newCursorPos)
  }

  const handleBlur = (event) => {
    const numericValue = parseNumber(event.target.value)
    displayValue.value = formatNumber(numericValue)
  }

  return {
    displayValue,
    handleInput,
    handleBlur,
    formatNumber,
    parseNumber
  }
}
