import DashboardLayout from '@/app/layouts/DashboardLayout';
import EmptyState from '@/components/common/EmptyState';

export default function MembersIndex() {
    return (
        <DashboardLayout breadcrumbs={[{ label: 'Members' }]} title="Members">
            <EmptyState title="Members" />
        </DashboardLayout>
    );
}
