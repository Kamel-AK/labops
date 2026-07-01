import DashboardLayout from '@/app/layouts/DashboardLayout';
import EmptyState from '@/components/common/EmptyState';

export default function SpotsIndex() {
    return (
        <DashboardLayout breadcrumbs={[{ label: 'Spots' }]} title="Spots">
            <EmptyState title="Spots" />
        </DashboardLayout>
    );
}
