import { Input } from '@/components/ui/input';
import { Search } from 'lucide-react';

export default function SearchBar({ className, ...props }) {
    return (
        <div className={className}>
            <div className="relative">
                <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input className="pl-9" type="search" {...props} />
            </div>
        </div>
    );
}
