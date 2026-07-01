import DashboardLayout from '@/app/layouts/DashboardLayout';
import EmptyState from '@/components/common/EmptyState';

export default function NotificationsIndex() {
    return (
        <DashboardLayout
            breadcrumbs={[{ label: 'Notifications' }]}
            title="Notifications"
        >
            <EmptyState title="Notifications" />
        </DashboardLayout>
    );
}
