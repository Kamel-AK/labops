import { cn } from '@/app/lib/utils';

export default function PageContainer({ children, className }) {
    return (
        <main
            className={cn(
                'mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8',
                className,
            )}
        >
            {children}
        </main>
    );
}
