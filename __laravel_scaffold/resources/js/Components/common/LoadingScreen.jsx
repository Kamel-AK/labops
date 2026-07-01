import LoadingSpinner from '@/components/common/LoadingSpinner';

export default function LoadingScreen() {
    return (
        <div className="flex min-h-screen items-center justify-center bg-background">
            <LoadingSpinner className="h-8 w-8" />
        </div>
    );
}
