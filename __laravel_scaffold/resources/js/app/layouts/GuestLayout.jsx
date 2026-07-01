import { appConfig } from '@/app/config/app';
import { ICONS } from '@/app/constants/icons';

export default function GuestLayout({ children }) {
    const Logo = ICONS.system;

    return (
        <div className="flex min-h-screen flex-col bg-muted/30">
            <main className="flex flex-1 items-center justify-center px-4 py-10">
                <div className="w-full max-w-md space-y-6">
                    <div className="flex items-center justify-center gap-3">
                        <span className="flex h-10 w-10 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                            <Logo className="h-5 w-5" />
                        </span>
                        <span className="text-xl font-semibold tracking-normal">
                            {appConfig.name}
                        </span>
                    </div>
                    <div className="rounded-lg border bg-background p-6 shadow-sm">
                        {children}
                    </div>
                </div>
            </main>
        </div>
    );
}
