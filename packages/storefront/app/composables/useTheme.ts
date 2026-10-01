import { computed } from 'vue'
import { useSettingsStore } from '~/stores/settings'

const hexToRgb = (hex: string): [number, number, number] => {
  const clean = hex.replace('#', '').trim()
  if (clean.length === 3) {
    return [
      parseInt(clean[0]! + clean[0]!, 16),
      parseInt(clean[1]! + clean[1]!, 16),
      parseInt(clean[2]! + clean[2]!, 16)
    ]
  }
  if (clean.length === 6) {
    return [
      parseInt(clean.slice(0, 2), 16),
      parseInt(clean.slice(2, 4), 16),
      parseInt(clean.slice(4, 6), 16)
    ]
  }
  return [225, 29, 72]
}

const rgbToHex = (r: number, g: number, b: number): string => {
  const clamp = (v: number) => Math.max(0, Math.min(255, Math.round(v)))
  return `#${clamp(r).toString(16).padStart(2, '0')}${clamp(g).toString(16).padStart(2, '0')}${clamp(b).toString(16).padStart(2, '0')}`
}

const generateShades = (baseHex: string) => {
  const [r, g, b] = hexToRgb(baseHex)
  const tint = (factor: number) => rgbToHex(r + (255 - r) * factor, g + (255 - g) * factor, b + (255 - b) * factor)
  const shade = (factor: number) => rgbToHex(r * (1 - factor), g * (1 - factor), b * (1 - factor))

  return {
    50: tint(0.92),
    100: tint(0.82),
    200: tint(0.65),
    300: tint(0.45),
    400: tint(0.25),
    500: baseHex,
    600: shade(0.12),
    700: shade(0.28),
    800: shade(0.45),
    900: shade(0.62),
    950: shade(0.78)
  }
}

export const useTheme = () => {
  const settingsStore = useSettingsStore()

  const themeCssVariables = computed(() => {
    const theme = settingsStore.settings.theme
    if (!theme) return ''

    const radius = theme.border_radius || '0.25rem'
    const primary = theme.primary_color || '#0284c7'
    const secondary = theme.secondary_color || '#0f172a'
    const fontScale = theme.font_scale || '1rem'

    const pShades = generateShades(primary)
    const sShades = generateShades(secondary)

    let shadowValue = '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)'
    if (theme.shadow_scale === 'none') shadowValue = 'none'
    else if (theme.shadow_scale === 'sm') shadowValue = '0 1px 2px 0 rgb(0 0 0 / 0.05)'
    else if (theme.shadow_scale === 'lg') shadowValue = '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)'

    let blurValue = '12px'
    if (theme.blur_scale === 'none') blurValue = '0px'
    else if (theme.blur_scale === 'sm') blurValue = '4px'
    else if (theme.blur_scale === 'lg') blurValue = '24px'

    return `
      :root {
        --ui-radius: ${radius};
        --theme-primary: ${primary};
        --theme-secondary: ${secondary};
        --ui-primary: ${primary};
        --ui-secondary: ${secondary};
        --color-primary: ${primary};
        --color-secondary: ${secondary};

        --ui-color-primary-50: ${pShades[50]};
        --ui-color-primary-100: ${pShades[100]};
        --ui-color-primary-200: ${pShades[200]};
        --ui-color-primary-300: ${pShades[300]};
        --ui-color-primary-400: ${pShades[400]};
        --ui-color-primary-500: ${pShades[500]};
        --ui-color-primary-600: ${pShades[600]};
        --ui-color-primary-700: ${pShades[700]};
        --ui-color-primary-800: ${pShades[800]};
        --ui-color-primary-900: ${pShades[900]};
        --ui-color-primary-950: ${pShades[950]};

        --color-primary-50: ${pShades[50]};
        --color-primary-100: ${pShades[100]};
        --color-primary-200: ${pShades[200]};
        --color-primary-300: ${pShades[300]};
        --color-primary-400: ${pShades[400]};
        --color-primary-500: ${pShades[500]};
        --color-primary-600: ${pShades[600]};
        --color-primary-700: ${pShades[700]};
        --color-primary-800: ${pShades[800]};
        --color-primary-900: ${pShades[900]};
        --color-primary-950: ${pShades[950]};

        --ui-color-secondary-50: ${sShades[50]};
        --ui-color-secondary-500: ${sShades[500]};
        --ui-color-secondary-600: ${sShades[600]};
        --color-secondary-50: ${sShades[50]};
        --color-secondary-500: ${sShades[500]};
        --color-secondary-600: ${sShades[600]};

        --theme-shadow: ${shadowValue};
        --theme-blur: ${blurValue};
        --theme-font-scale: ${fontScale};
      }
    `
  })

  return {
    themeCssVariables
  }
}
