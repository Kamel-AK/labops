import { ConfirmProvider } from '@/app/contexts/ConfirmContext';
import QueryProvider from '@/app/providers/QueryProvider';
import { Toaster } from 'sonner';

export default function AppProviders({ children }) {
    return (
        <QueryProvider>
            <ConfirmProvider>
                {children}
                <Toaster closeButton richColors position="top-right" />
            </ConfirmProvider>
        </QueryProvider>
    );
}
