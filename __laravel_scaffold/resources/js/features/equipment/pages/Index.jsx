import DashboardLayout from '@/app/layouts/DashboardLayout';
import EmptyState from '@/components/common/EmptyState';

export default function EquipmentIndex() {
    return (
        <DashboardLayout
            breadcrumbs={[{ label: 'Equipment' }]}
            title="Equipment"
        >
            <EmptyState title="Equipment" />
        </DashboardLayout>
    );
}
