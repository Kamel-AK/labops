import { useMemo, useState } from 'react';

export function usePagination(initialPage = 1, initialPerPage = 15) {
    const [page, setPage] = useState(initialPage);
    const [perPage, setPerPage] = useState(initialPerPage);

    return useMemo(
        () => ({ page, perPage, setPage, setPerPage }),
        [page, perPage],
    );
}
