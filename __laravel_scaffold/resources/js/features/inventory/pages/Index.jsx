import DashboardLayout from '@/app/layouts/DashboardLayout';
import EmptyState from '@/components/common/EmptyState';

export default function InventoryIndex() {
    return (
        <DashboardLayout
            breadcrumbs={[{ label: 'Inventory' }]}
            title="Inventory"
        >
            <EmptyState title="Inventory" />
        </DashboardLayout>
    );
}
