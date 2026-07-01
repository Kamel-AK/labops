import { cn } from '@/app/lib/utils';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-react';

export default function Pagination({
    canNextPage = false,
    canPreviousPage = false,
    onNextPage,
    onPreviousPage,
    page = 1,
    className,
}) {
    return (
        <nav
            className={cn('flex items-center justify-end gap-2', className)}
            aria-label="Pagination"
        >
            <Button
                type="button"
                variant="outline"
                size="icon"
                disabled={!canPreviousPage}
                onClick={onPreviousPage}
            >
                <ChevronLeft className="h-4 w-4" />
                <span className="sr-only">Previous page</span>
            </Button>
            <span className="min-w-10 text-center text-sm text-muted-foreground">
                {page}
            </span>
            <Button
                type="button"
                variant="outline"
                size="icon"
                disabled={!canNextPage}
                onClick={onNextPage}
            >
                <ChevronRight className="h-4 w-4" />
                <span className="sr-only">Next page</span>
            </Button>
        </nav>
    );
}
