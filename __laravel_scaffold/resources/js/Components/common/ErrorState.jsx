import { cn } from '@/app/lib/utils';
import { AlertTriangle } from 'lucide-react';

export default function ErrorState({
    className,
    description,
    title = 'Something went wrong',
}) {
    return (
        <div
            className={cn(
                'flex min-h-64 flex-col items-center justify-center rounded-lg border border-destructive/40 p-8 text-center',
                className,
            )}
        >
            <AlertTriangle className="h-10 w-10 text-destructive" />
            <h2 className="mt-4 text-base font-semibold">{title}</h2>
            {description && (
                <p className="mt-2 max-w-md text-sm text-muted-foreground">
                    {description}
                </p>
            )}
        </div>
    );
}
