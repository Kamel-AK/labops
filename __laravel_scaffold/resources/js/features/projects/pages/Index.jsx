import DashboardLayout from '@/app/layouts/DashboardLayout';
import EmptyState from '@/components/common/EmptyState';

export default function ProjectsIndex() {
    return (
        <DashboardLayout breadcrumbs={[{ label: 'Projects' }]} title="Projects">
            <EmptyState title="Projects" />
        </DashboardLayout>
    );
}
