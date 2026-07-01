import {
    Activity,
    Bell,
    Boxes,
    CalendarDays,
    FlaskConical,
    FolderKanban,
    LayoutDashboard,
    Map,
    MapPin,
    Settings,
    Users,
    Wrench,
} from 'lucide-react';

export const ICONS = Object.freeze({
    activityLog: Activity,
    dashboard: LayoutDashboard,
    equipment: Wrench,
    inventory: Boxes,
    members: Users,
    notifications: Bell,
    projects: FolderKanban,
    reservations: CalendarDays,
    settings: Settings,
    spots: MapPin,
    system: FlaskConical,
    zones: Map,
});
