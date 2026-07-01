import { appConfig } from '@/app/config/app';
import { ICONS } from '@/app/constants/icons';
import { SIDEBAR_ITEMS } from '@/app/constants/sidebar';
import { cn } from '@/app/lib/utils';
import { Link, usePage } from '@inertiajs/react';
import { X } from 'lucide-react';

function SidebarContent({ onNavigate }) {
    const { url } = usePage();
    const Logo = ICONS.system;

    return (
        <>
            <div className="flex h-16 items-center gap-3 border-b px-6">
                <span className="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                    <Logo className="h-5 w-5" />
                </span>
                <span className="font-semibold tracking-normal">
                    {appConfig.name}
                </span>
            </div>
            <nav className="space-y-1 px-3 py-4">
                {SIDEBAR_ITEMS.map((item) => {
                    const Icon = item.icon;
                    const active =
                        url === item.href || url.startsWith(`${item.href}/`);

                    return (
                        <Link
                            key={item.href}
                            href={item.href}
                            onClick={onNavigate}
                            className={cn(
                                'flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground',
                                active && 'bg-muted text-foreground',
                            )}
                        >
                            <Icon className="h-4 w-4" />
                            {item.label}
                        </Link>
                    );
                })}
            </nav>
        </>
    );
}

export default function Sidebar({ mobileOpen = false, onMobileClose }) {
    return (
        <>
            <aside className="fixed inset-y-0 left-0 z-40 hidden w-72 border-r bg-background lg:block">
                <SidebarContent />
            </aside>

            {mobileOpen && (
                <div className="fixed inset-0 z-50 lg:hidden">
                    <button
                        type="button"
                        className="absolute inset-0 bg-black/60"
                        onClick={onMobileClose}
                    >
                        <span className="sr-only">Close navigation</span>
                    </button>
                    <aside className="relative h-full w-72 border-r bg-background shadow-xl">
                        <button
                            type="button"
                            className="absolute right-3 top-3 inline-flex h-9 w-9 items-center justify-center rounded-md text-muted-foreground hover:bg-muted hover:text-foreground"
                            onClick={onMobileClose}
                        >
                            <X className="h-5 w-5" />
                            <span className="sr-only">Close navigation</span>
                        </button>
                        <SidebarContent onNavigate={onMobileClose} />
                    </aside>
                </div>
            )}
        </>
    );
}
