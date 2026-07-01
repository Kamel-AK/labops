import ErrorLayout from '@/app/layouts/ErrorLayout';

export default function Forbidden() {
    return <ErrorLayout status="403" title="Forbidden" />;
}
