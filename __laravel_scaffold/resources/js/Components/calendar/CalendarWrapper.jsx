import { cn } from '@/app/lib/utils';
import { DayPicker } from 'react-day-picker';
import 'react-day-picker/style.css';

export default function CalendarWrapper({ className, ...props }) {
    return (
        <DayPicker
            className={cn('rounded-lg border bg-background p-3', className)}
            {...props}
        />
    );
}
