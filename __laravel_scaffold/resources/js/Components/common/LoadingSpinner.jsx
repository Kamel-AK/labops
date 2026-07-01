import { cn } from '@/app/lib/utils';
import { LoaderCircle } from 'lucide-react';

export default function LoadingSpinner({ className }) {
    return (
        <LoaderCircle
            className={cn(
                'h-5 w-5 animate-spin text-muted-foreground',
                className,
            )}
        />
    );
}
