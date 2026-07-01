import DashboardLayout from '@/app/layouts/DashboardLayout';
import EmptyState from '@/components/common/EmptyState';

export default function ReservationsIndex() {
    return (
        <DashboardLayout
            breadcrumbs={[{ label: 'Reservations' }]}
            title="Reservations"
        >
            <EmptyState title="Reservations" />
        </DashboardLayout>
    );
}
