import DashboardLayout from '@/app/layouts/DashboardLayout';
import EmptyState from '@/components/common/EmptyState';

export default function DashboardIndex() {
    return (
        <DashboardLayout title="Dashboard">
            <EmptyState title="Dashboard" />
        </DashboardLayout>
    );
}
