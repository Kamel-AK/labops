import { cn } from '@/app/lib/utils';

export default function ContentWrapper({ children, className }) {
    return <div className={cn('space-y-6', className)}>{children}</div>;
}
