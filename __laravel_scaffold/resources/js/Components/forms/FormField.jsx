import { cn } from '@/app/lib/utils';

export default function FormField({ children, className }) {
    return <div className={cn('space-y-2', className)}>{children}</div>;
}
