import { ref } from 'vue'

const isDark = ref(document.documentElement.classList.contains('dark'))

export function useTheme() {
    const setTheme = (theme) => {
        const dark = theme === 'dark'
        document.documentElement.classList.toggle('dark', dark)
        localStorage.setItem('theme', theme)
        isDark.value = dark
    }

    const toggleTheme = () => setTheme(isDark.value ? 'light' : 'dark')

    return { isDark, toggleTheme, setTheme }
}
