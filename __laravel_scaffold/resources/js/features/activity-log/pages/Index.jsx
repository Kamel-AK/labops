import DashboardLayout from '@/app/layouts/DashboardLayout';
import EmptyState from '@/components/common/EmptyState';

export default function ActivityLogIndex() {
    return (
        <DashboardLayout
            breadcrumbs={[{ label: 'Activity Log' }]}
            title="Activity Log"
        >
            <EmptyState title="Activity Log" />
        </DashboardLayout>
    );
}
