import DashboardLayout from '@/app/layouts/DashboardLayout';

export default function AuthenticatedLayout({ children, header }) {
    return (
        <DashboardLayout>
            {header && <div className="mb-6">{header}</div>}
            {children}
        </DashboardLayout>
    );
}
