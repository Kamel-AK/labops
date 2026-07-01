import Footer from '@/app/layouts/Footer';
import Sidebar from '@/app/layouts/Sidebar';
import Topbar from '@/app/layouts/Topbar';
import Breadcrumb from '@/components/common/Breadcrumb';
import ContentWrapper from '@/components/common/ContentWrapper';
import PageContainer from '@/components/common/PageContainer';
import { useState } from 'react';

export default function DashboardLayout({ breadcrumbs = [], children, title }) {
    const [mobileSidebarOpen, setMobileSidebarOpen] = useState(false);

    return (
        <div className="min-h-screen bg-background">
            <Sidebar
                mobileOpen={mobileSidebarOpen}
                onMobileClose={() => setMobileSidebarOpen(false)}
            />
            <div className="flex min-h-screen flex-col lg:pl-72">
                <Topbar
                    title={title}
                    onMenuClick={() => setMobileSidebarOpen(true)}
                />
                <PageContainer className="flex-1">
                    <ContentWrapper>
                        <div className="space-y-2">
                            <Breadcrumb items={breadcrumbs} />
                            {title && (
                                <h1 className="text-2xl font-semibold tracking-normal">
                                    {title}
                                </h1>
                            )}
                        </div>
                        {children}
                    </ContentWrapper>
                </PageContainer>
                <Footer />
            </div>
        </div>
    );
}
