import { cn } from '@/app/lib/utils';
import { Inbox } from 'lucide-react';

export default function EmptyState({
    action,
    className,
    description,
    icon: Icon = Inbox,
    title = 'No results',
}) {
    return (
        <div
            className={cn(
                'flex min-h-64 flex-col items-center justify-center rounded-lg border border-dashed p-8 text-center',
                className,
            )}
        >
            <Icon className="h-10 w-10 text-muted-foreground" />
            <h2 className="mt-4 text-base font-semibold">{title}</h2>
            {description && (
                <p className="mt-2 max-w-md text-sm text-muted-foreground">
                    {description}
                </p>
            )}
            {action && <div className="mt-6">{action}</div>}
        </div>
    );
}
