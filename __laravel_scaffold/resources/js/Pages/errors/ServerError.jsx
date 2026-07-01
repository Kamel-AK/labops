import ErrorLayout from '@/app/layouts/ErrorLayout';

export default function ServerError() {
    return <ErrorLayout status="500" title="Server error" />;
}
