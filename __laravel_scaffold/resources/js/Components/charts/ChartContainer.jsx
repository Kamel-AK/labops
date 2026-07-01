import { cn } from '@/app/lib/utils';
import { ResponsiveContainer } from 'recharts';

export default function ChartContainer({ children, className, height = 320 }) {
    return (
        <div className={cn('w-full', className)} style={{ height }}>
            <ResponsiveContainer width="100%" height="100%">
                {children}
            </ResponsiveContainer>
        </div>
    );
}
