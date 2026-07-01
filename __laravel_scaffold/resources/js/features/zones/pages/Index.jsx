import DashboardLayout from '@/app/layouts/DashboardLayout';
import EmptyState from '@/components/common/EmptyState';

export default function ZonesIndex() {
    return (
        <DashboardLayout breadcrumbs={[{ label: 'Zones' }]} title="Zones">
            <EmptyState title="Zones" />
        </DashboardLayout>
    );
}
