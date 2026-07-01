import DashboardLayout from '@/app/layouts/DashboardLayout';
import EmptyState from '@/components/common/EmptyState';

export default function SettingsIndex() {
    return (
        <DashboardLayout breadcrumbs={[{ label: 'Settings' }]} title="Settings">
            <EmptyState title="Settings" />
        </DashboardLayout>
    );
}
