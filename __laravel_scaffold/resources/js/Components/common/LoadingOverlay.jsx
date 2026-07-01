import LoadingSpinner from '@/components/common/LoadingSpinner';

export default function LoadingOverlay({ show = false }) {
    if (!show) {
        return null;
    }

    return (
        <div className="absolute inset-0 z-10 flex items-center justify-center bg-background/80 backdrop-blur-sm">
            <LoadingSpinner className="h-8 w-8" />
        </div>
    );
}
