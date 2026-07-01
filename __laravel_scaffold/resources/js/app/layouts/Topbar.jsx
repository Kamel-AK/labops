import { ROUTES } from '@/app/constants/routes';
import { useAuth } from '@/app/hooks/useAuth';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Link, router } from '@inertiajs/react';
import { LogOut, Menu, User } from 'lucide-react';

export default function Topbar({ onMenuClick, title }) {
    const { user } = useAuth();
    const initials = user?.name
        ?.split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();

    return (
        <header className="sticky top-0 z-30 flex h-16 items-center justify-between border-b bg-background/95 px-4 backdrop-blur sm:px-6 lg:px-8">
            <div className="flex items-center gap-3">
                <Button
                    className="lg:hidden"
                    size="icon"
                    type="button"
                    variant="ghost"
                    onClick={onMenuClick}
                >
                    <Menu className="h-5 w-5" />
                    <span className="sr-only">Navigation</span>
                </Button>
                {title && (
                    <span className="text-sm font-medium text-muted-foreground">
                        {title}
                    </span>
                )}
            </div>

            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button className="gap-2" variant="ghost">
                        <Avatar className="h-8 w-8">
                            <AvatarFallback>{initials || 'LO'}</AvatarFallback>
                        </Avatar>
                        <span className="hidden max-w-40 truncate sm:inline">
                            {user?.name ?? 'LabOps'}
                        </span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    <DropdownMenuLabel>
                        {user?.email ?? 'Account'}
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem asChild>
                        <Link href={ROUTES.profile}>
                            <User className="mr-2 h-4 w-4" />
                            Profile
                        </Link>
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        onSelect={(event) => {
                            event.preventDefault();
                            router.post('/logout');
                        }}
                    >
                        <LogOut className="mr-2 h-4 w-4" />
                        Logout
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </header>
    );
}
