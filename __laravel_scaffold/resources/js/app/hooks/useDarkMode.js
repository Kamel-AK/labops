import { useLocalStorage } from '@/app/hooks/useLocalStorage';
import { useEffect } from 'react';

export function useDarkMode() {
    const [enabled, setEnabled] = useLocalStorage('labops:dark-mode', false);

    useEffect(() => {
        document.documentElement.classList.toggle('dark', enabled);
    }, [enabled]);

    return [enabled, setEnabled];
}
