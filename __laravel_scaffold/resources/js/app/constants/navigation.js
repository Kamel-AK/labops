import { ICONS } from '@/app/constants/icons';
import { ROUTES } from '@/app/constants/routes';

export const NAVIGATION_ITEMS = Object.freeze([
    { label: 'Dashboard', href: ROUTES.dashboard, icon: ICONS.dashboard },
    { label: 'Members', href: ROUTES.members, icon: ICONS.members },
    { label: 'Projects', href: ROUTES.projects, icon: ICONS.projects },
    {
        label: 'Reservations',
        href: ROUTES.reservations,
        icon: ICONS.reservations,
    },
    { label: 'Zones', href: ROUTES.zones, icon: ICONS.zones },
    { label: 'Spots', href: ROUTES.spots, icon: ICONS.spots },
    { label: 'Equipment', href: ROUTES.equipment, icon: ICONS.equipment },
    { label: 'Inventory', href: ROUTES.inventory, icon: ICONS.inventory },
    {
        label: 'Notifications',
        href: ROUTES.notifications,
        icon: ICONS.notifications,
    },
    {
        label: 'Activity Log',
        href: ROUTES.activityLog,
        icon: ICONS.activityLog,
    },
    { label: 'Settings', href: ROUTES.settings, icon: ICONS.settings },
]);
