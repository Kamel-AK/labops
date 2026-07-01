import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';

export default function Breadcrumb({ items = [] }) {
    if (items.length === 0) {
        return null;
    }

    return (
        <nav className="flex items-center gap-1 text-sm text-muted-foreground">
            {items.map((item, index) => {
                const isLast = index === items.length - 1;

                return (
                    <div
                        key={`${item.label}-${index}`}
                        className="flex items-center gap-1"
                    >
                        {item.href && !isLast ? (
                            <Link
                                className="hover:text-foreground"
                                href={item.href}
                            >
                                {item.label}
                            </Link>
                        ) : (
                            <span
                                className={
                                    isLast ? 'text-foreground' : undefined
                                }
                            >
                                {item.label}
                            </span>
                        )}
                        {!isLast && <ChevronRight className="h-4 w-4" />}
                    </div>
                );
            })}
        </nav>
    );
}
