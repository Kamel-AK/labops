import { useCallback, useState } from 'react';

export function useFilters(initialFilters = {}) {
    const [filters, setFilters] = useState(initialFilters);

    const updateFilter = useCallback((key, value) => {
        setFilters((current) => ({ ...current, [key]: value }));
    }, []);

    const resetFilters = useCallback(() => {
        setFilters(initialFilters);
    }, [initialFilters]);

    return { filters, resetFilters, setFilters, updateFilter };
}
