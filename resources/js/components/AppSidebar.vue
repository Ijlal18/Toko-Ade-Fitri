<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ArrowLeftRight,
    BarChart3,
    Boxes,
    Building2,
    CreditCard,
    FileSpreadsheet,
    LayoutGrid,
    Package,
    Receipt,
    Tags,
    UserCog,
    Users,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const page = usePage();

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Kasir POS',
            href: '/pos',
            icon: CreditCard,
        },
        {
            title: 'Produk Toko',
            href: '/products',
            icon: Package,
        },
        {
            title: 'Kategori',
            href: '/categories',
            icon: Tags,
        },
        {
            title: 'Supplier',
            href: '/suppliers',
            icon: Building2,
        },
        {
            title: 'Tagihan Supplier',
            href: '/supplier-invoices',
            icon: FileSpreadsheet,
        },
        {
            title: 'Pelanggan',
            href: '/customers',
            icon: Users,
        },
        {
            title: 'Kelola Stok',
            href: '/stock',
            icon: ArrowLeftRight,
        },
        {
            title: 'Riwayat Transaksi',
            href: '/transactions',
            icon: Receipt,
        },
        {
            title: 'Laporan Toko',
            href: '/reports',
            icon: BarChart3,
        },
    ];

    const userRole = page.props.auth?.user?.role;
    if (userRole !== 'Karyawan') {
        items.push({
            title: 'Kelola Staff',
            href: '/users',
            icon: UserCog,
        });
    }

    return items;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader class="p-3 border-b border-sidebar-border/50">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
