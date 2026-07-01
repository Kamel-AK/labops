import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';

export default function ErrorLayout({ description, status, title }) {
    return (
        <div className="flex min-h-screen items-center justify-center bg-background px-4 py-10">
            <div className="max-w-md text-center">
                <p className="text-sm font-semibold text-secondary">{status}</p>
                <h1 className="mt-3 text-3xl font-semibold tracking-normal">
                    {title}
                </h1>
                {description && (
                    <p className="mt-4 text-sm text-muted-foreground">
                        {description}
                    </p>
                )}
                <Button asChild className="mt-6">
                    <Link href="/dashboard">Dashboard</Link>
                </Button>
            </div>
        </div>
    );
}
