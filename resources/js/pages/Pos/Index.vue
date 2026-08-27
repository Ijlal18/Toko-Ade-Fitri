<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import {
    CheckCircle2,
    CreditCard,
    Minus,
    Plus,
    Printer,
    QrCode,
    Receipt,
    RotateCcw,
    Search,
    ShoppingCart,
    Tag,
    Trash2,
    User,
    X,
} from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { toast } from 'vue-sonner';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

interface Category {
    id: number;
    name: string;
    slug: string;
}

interface Product {
    id: number;
    sku: string;
    barcode: string;
    name: string;
    selling_price_retail: number;
    selling_price_wholesale: number;
    stock: number;
    unit: string;
    category_id: number;
    notes?: string | null;
    category?: Category;
}

interface Customer {
    id: number;
    name: string;
    type: 'retail' | 'wholesale';
}

interface CartItem {
    product: Product;
    price_type: 'retail' | 'wholesale';
    unit_price: number;
    quantity: number;
}

const props = defineProps<{
    products: Product[];
    categories: Category[];
    customers: Customer[];
}>();

// State
const searchQuery = ref('');
const selectedCategory = ref<number | null>(null);
const selectedCustomer = ref<Customer | null>(null);
const cart = ref<CartItem[]>([]);
const discountInput = ref(0);
const taxInput = ref(0);
const showPaymentModal = ref(false);
const selectedPaymentMethod = ref<'cash' | 'qris'>('cash');
const paidAmountInput = ref(0);
const isQrisVerified = ref(false);
const showReceiptModal = ref(false);
const completedTransaction = ref<any>(null);

// Flash messages check
const page = usePage();
watch(
    () => page.props.flash,
    (flash: any) => {
        if (flash?.transaction) {
            completedTransaction.value = flash.transaction;
            showReceiptModal.value = true;
            cart.value = [];
            discountInput.value = 0;
            taxInput.value = 0;
            paidAmountInput.value = 0;
            selectedCustomer.value = null;
            searchQuery.value = '';
            selectedCategory.value = null;
            showPaymentModal.value = false;
            toast.success('Pembayaran Berhasil Diverifikasi!', {
                description: `Invoice: ${flash.transaction.invoice_number} • Kembalian: ${formatRupiah(flash.transaction.change_amount)}`,
            });
        } else if (flash?.error) {
            toast.error('Pembayaran Gagal!', {
                description: flash.error,
            });
            showPaymentModal.value = false;
        }
    },
    { immediate: true, deep: true }
);

// Filtered Products
const filteredProducts = computed(() => {
    return props.products.filter((p) => {
        const matchesSearch =
            searchQuery.value === '' ||
            p.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            p.sku.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (p.barcode && p.barcode.includes(searchQuery.value));

        const matchesCat = selectedCategory.value === null || p.category_id === selectedCategory.value;

        return matchesSearch && matchesCat;
    });
});

// Auto Select Price Type Based on Customer
const currentCustomerType = computed(() => selectedCustomer.value?.type || 'retail');

// Stock validation helpers
const hasStockError = computed(() => {
    return cart.value.some((item) => item.quantity > item.product.stock || item.product.stock <= 0);
});

const addToCart = (product: Product) => {
    if (product.stock <= 0) {
        toast.error('Stok Produk Habis!', {
            description: `Produk "${product.name}" sudah habis (stok: 0) dan tidak dapat dibeli.`,
        });
        return;
    }

    const existingIndex = cart.value.findIndex((item) => item.product.id === product.id);

    if (existingIndex > -1) {
        if (cart.value[existingIndex].quantity < product.stock) {
            cart.value[existingIndex].quantity++;
        } else {
            toast.warning('Stok Tidak Mencukupi!', {
                description: `Jumlah pembelian "${product.name}" mencapai batas stok (${product.stock} ${product.unit}).`,
            });
        }
    } else {
        const priceType = currentCustomerType.value;
        const unitPrice =
            priceType === 'wholesale' ? Number(product.selling_price_wholesale) : Number(product.selling_price_retail);

        cart.value.push({
            product,
            price_type: priceType,
            unit_price: unitPrice,
            quantity: 1,
        });
    }
};

const updateItemQuantity = (index: number, delta: number) => {
    const item = cart.value[index];
    const newQty = item.quantity + delta;

    if (newQty <= 0) {
        cart.value.splice(index, 1);
    } else if (newQty <= item.product.stock) {
        item.quantity = newQty;
    } else {
        toast.warning('Stok Tidak Mencukupi!', {
            description: `Jumlah pembelian "${item.product.name}" melebihi stok tersedia (${item.product.stock} ${item.product.unit}).`,
        });
    }
};

const toggleItemPriceType = (index: number) => {
    const item = cart.value[index];
    if (item.price_type === 'retail') {
        item.price_type = 'wholesale';
        item.unit_price = Number(item.product.selling_price_wholesale);
    } else {
        item.price_type = 'retail';
        item.unit_price = Number(item.product.selling_price_retail);
    }
};

const removeFromCart = (index: number) => {
    cart.value.splice(index, 1);
};

const clearCart = () => {
    cart.value = [];
};

// Calculations
const cartSubtotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.unit_price * item.quantity, 0);
});

const grandTotal = computed(() => {
    return Math.max(0, cartSubtotal.value - Number(discountInput.value) + Number(taxInput.value));
});

const changeAmount = computed(() => {
    return Math.max(0, Number(paidAmountInput.value) - grandTotal.value);
});

const formatRupiah = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val);
};

const quickMoneyOptions = computed(() => {
    const total = grandTotal.value;
    const options = [total];
    if (total <= 50000) options.push(50000);
    if (total <= 100000) options.push(100000);
    if (total <= 200000) options.push(200000);
    return Array.from(new Set(options)).sort((a, b) => a - b);
});

const setQuickMoney = (amount: number) => {
    paidAmountInput.value = amount;
};

const openPaymentModal = () => {
    if (cart.value.length === 0) return;

    const invalidItem = cart.value.find((item) => item.quantity > item.product.stock || item.product.stock <= 0);
    if (invalidItem) {
        toast.error('Pembelian Tidak Bisa Diproses!', {
            description: `Stok produk "${invalidItem.product.name}" tidak mencukupi (di keranjang: ${invalidItem.quantity}, sisa stok: ${invalidItem.product.stock} ${invalidItem.product.unit}).`,
        });
        return;
    }

    showPaymentModal.value = true;
};

// Checkout Form Submission
const form = useForm({
    customer_id: null as number | null,
    payment_method: 'cash',
    discount_amount: 0,
    tax_amount: 0,
    paid_amount: 0,
    notes: '',
    items: [] as any[],
});

const processCheckout = () => {
    const invalidItem = cart.value.find((item) => item.quantity > item.product.stock || item.product.stock <= 0);
    if (invalidItem) {
        toast.error('Pembayaran Gagal & Dibatalkan!', {
            description: `Stok produk "${invalidItem.product.name}" tidak mencukupi (sisa stok: ${invalidItem.product.stock} ${invalidItem.product.unit}).`,
        });
        showPaymentModal.value = false;
        return;
    }

    form.customer_id = selectedCustomer.value?.id || null;
    form.payment_method = selectedPaymentMethod.value;
    form.discount_amount = discountInput.value;
    form.tax_amount = taxInput.value;
    form.paid_amount = paidAmountInput.value;
    form.items = cart.value.map((item) => ({
        product_id: item.product.id,
        price_type: item.price_type,
        unit_price: item.unit_price,
        quantity: item.quantity,
    }));

    form.post('/pos', {
        preserveScroll: true,
        onError: (errors) => {
            const firstErr = Object.values(errors)[0] || 'Gagal memproses pembayaran karena kendala stok.';
            toast.error('Pembayaran Gagal!', {
                description: String(firstErr),
            });
            showPaymentModal.value = false;
        },
    });
};

const closeReceiptModal = () => {
    showReceiptModal.value = false;
};

const printReceipt = () => {
    window.print();
    showReceiptModal.value = false;
};

const handleAfterPrint = () => {
    showReceiptModal.value = false;
};

onMounted(() => {
    if (typeof window !== 'undefined') {
        window.addEventListener('afterprint', handleAfterPrint);
    }
});

onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('afterprint', handleAfterPrint);
    }
});
</script>

<template>
    <Head title="Kasir Point of Sale (POS) - Toko Ade Fitri" />

    <div class="flex h-[calc(100vh-4rem)] flex-col lg:flex-row overflow-hidden bg-slate-100 dark:bg-slate-950">
        <!-- LEFT COL: Product Catalog & Search -->
        <div class="flex flex-1 flex-col overflow-hidden p-4 lg:p-6">
            <!-- Search & Filters Header -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-4">
                <div class="relative w-full sm:w-80">
                    <Search class="absolute left-3 top-3 h-4 w-4 text-slate-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari barang, barcode, SKU..."
                        class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-4 text-xs font-semibold shadow-sm focus:border-blue-500 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                    />
                </div>

                <!-- Category Pills Scrollable -->
                <div class="flex w-full sm:w-auto items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none">
                    <button
                        @click="selectedCategory = null"
                        class="rounded-lg px-3 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="
                            selectedCategory === null
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                                : 'bg-white text-slate-600 hover:bg-slate-200 dark:bg-slate-900 dark:text-slate-300'
                        "
                    >
                        Semua Kategori
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        @click="selectedCategory = cat.id"
                        class="rounded-lg px-3 py-2 text-xs font-bold whitespace-nowrap transition-all"
                        :class="
                            selectedCategory === cat.id
                                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                                : 'bg-white text-slate-600 hover:bg-slate-200 dark:bg-slate-900 dark:text-slate-300'
                        "
                    >
                        {{ cat.name }}
                    </button>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="flex-1 overflow-y-auto pr-1">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">
                    <div
                        v-for="prod in filteredProducts"
                        :key="prod.id"
                        @click="addToCart(prod)"
                        class="group relative flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm transition-all hover:border-blue-500 hover:shadow-md cursor-pointer select-none dark:border-slate-800 dark:bg-slate-900"
                        :class="prod.stock <= 0 ? 'opacity-60 bg-red-50/20 dark:bg-red-950/10 border-red-200 dark:border-red-950' : ''"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-1 mb-1">
                                <span class="rounded bg-blue-50 px-1.5 py-0.5 text-[9px] font-bold text-blue-600 dark:bg-blue-950 dark:text-blue-400">
                                    {{ prod.category?.name || 'Umum' }}
                                </span>
                                <span
                                    class="rounded px-1.5 py-0.5 text-[9px] font-black"
                                    :class="prod.stock <= 0 ? 'bg-red-600 text-white' : (prod.stock <= 5 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700')"
                                >
                                    {{ prod.stock <= 0 ? 'HABIS' : `Stok ${prod.stock} ${prod.unit}` }}
                                </span>
                            </div>
                            <h3 class="line-clamp-2 text-xs font-bold text-slate-800 group-hover:text-blue-600 dark:text-slate-200 dark:group-hover:text-blue-400">
                                {{ prod.name }}
                            </h3>
                            <p class="mt-0.5 text-[10px] font-mono text-slate-400">SKU: {{ prod.sku }}</p>
                            <p v-if="prod.notes" class="mt-1 text-[10px] text-amber-600 dark:text-amber-400 font-medium whitespace-pre-line wrap-break-word leading-tight bg-amber-50/70 dark:bg-amber-950/40 p-1 rounded border border-amber-200/60 dark:border-amber-900/50">
                                📝 {{ prod.notes }}
                            </p>
                        </div>

                        <div class="mt-3 border-t border-slate-100 pt-2 dark:border-slate-800">
                            <!-- Retail vs Wholesale Display -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="block text-[9px] text-slate-400 uppercase font-semibold">Eceran</span>
                                    <span class="text-xs font-extrabold text-blue-600 dark:text-blue-400">
                                        {{ formatRupiah(prod.selling_price_retail) }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-[9px] text-emerald-600 uppercase font-bold">Grosir</span>
                                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                        {{ formatRupiah(prod.selling_price_wholesale) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="filteredProducts.length === 0" class="flex h-64 flex-col items-center justify-center text-slate-400">
                    <Search class="h-8 w-8 mb-2 opacity-50" />
                    <p class="text-xs font-bold">Tidak ada produk ditemukan</p>
                </div>
            </div>
        </div>

        <!-- RIGHT COL: Cart Sidebar -->
        <div class="w-full lg:w-96 flex flex-col border-l border-slate-200 bg-white shadow-lg dark:border-slate-800 dark:bg-slate-900">
            <!-- Customer Selector Bar -->
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <User class="h-3.5 w-3.5 text-blue-600" /> Pelanggan
                    </span>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded" :class="currentCustomerType === 'wholesale' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700'">
                        Harga {{ currentCustomerType === 'wholesale' ? 'GROSIR' : 'ECERAN' }}
                    </span>
                </div>
                <select
                    v-model="selectedCustomer"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2 px-3 text-xs font-bold text-slate-800 shadow-sm focus:border-blue-500 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                >
                    <option :value="null">-- Pelanggan Umum (Eceran) --</option>
                    <option v-for="c in customers" :key="c.id" :value="c">
                        {{ c.name }} ({{ c.type === 'wholesale' ? 'Grosir' : 'Eceran' }})
                    </option>
                </select>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <div v-if="cart.length === 0" class="flex h-full flex-col items-center justify-center text-slate-400 py-12">
                    <ShoppingCart class="h-10 w-10 mb-2 opacity-30" />
                    <p class="text-xs font-bold">Keranjang Belanja Kosong</p>
                    <p class="text-[10px]">Klik produk di sebelah kiri untuk menambah</p>
                </div>

                <div
                    v-for="(item, idx) in cart"
                    :key="idx"
                    class="flex flex-col gap-2 rounded-xl border p-3 transition-all"
                    :class="item.quantity > item.product.stock || item.product.stock <= 0 ? 'border-red-300 bg-red-50/80 dark:border-red-800 dark:bg-red-950/40' : 'border-slate-100 bg-slate-50/80 dark:border-slate-800 dark:bg-slate-800/40'"
                >
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                {{ item.product.name }}
                            </h4>
                            <span class="text-[10px] text-slate-400">SKU: {{ item.product.sku }}</span>
                        </div>
                        <button @click="removeFromCart(idx)" class="text-slate-400 hover:text-red-500">
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>
                    </div>

                    <!-- Warning if stock is insufficient -->
                    <div v-if="item.quantity > item.product.stock || item.product.stock <= 0" class="text-[10px] font-bold text-red-600 dark:text-red-400 flex items-center gap-1">
                        ⚠️ Stok tidak mencukupi (Sisa stok: {{ item.product.stock }} {{ item.product.unit }})
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <!-- Toggle Retail/Wholesale Button -->
                        <button
                            @click="toggleItemPriceType(idx)"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider"
                            :class="item.price_type === 'wholesale' ? 'bg-emerald-600 text-white' : 'bg-blue-600 text-white'"
                        >
                            <Tag class="h-2.5 w-2.5" />
                            {{ item.price_type === 'wholesale' ? 'Grosir' : 'Eceran' }}
                        </button>

                        <!-- Qty Controls -->
                        <div class="flex items-center gap-2">
                            <button
                                @click="updateItemQuantity(idx, -1)"
                                class="flex h-6 w-6 items-center justify-center rounded-lg bg-white shadow-sm border border-slate-200 font-bold text-slate-700 hover:bg-slate-100 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300"
                            >
                                <Minus class="h-3 w-3" />
                            </button>
                            <span class="w-6 text-center text-xs font-extrabold text-slate-900 dark:text-white">
                                {{ item.quantity }}
                            </span>
                            <button
                                @click="updateItemQuantity(idx, 1)"
                                class="flex h-6 w-6 items-center justify-center rounded-lg bg-white shadow-sm border border-slate-200 font-bold text-slate-700 hover:bg-slate-100 dark:bg-slate-900 dark:border-slate-700 dark:text-slate-300"
                            >
                                <Plus class="h-3 w-3" />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between text-xs pt-1 border-t border-slate-200/50 dark:border-slate-800">
                        <span class="text-slate-500 font-mono text-[10px]">@ {{ formatRupiah(item.unit_price) }}</span>
                        <span class="font-extrabold text-blue-600 dark:text-blue-400">
                            {{ formatRupiah(item.unit_price * item.quantity) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Cart Footer Summary & Checkout Button -->
            <div class="p-4 border-t border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900 space-y-3">
                <div class="space-y-1 text-xs">
                    <div class="flex justify-between text-slate-500">
                        <span>Subtotal</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-200">{{ formatRupiah(cartSubtotal) }}</span>
                    </div>

                    <!-- Discount & Tax Controls -->
                    <div class="flex justify-between items-center text-slate-500 pt-1">
                        <span>Potongan / Diskon (Rp)</span>
                        <input
                            v-model.number="discountInput"
                            type="number"
                            min="0"
                            class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-right text-xs font-bold text-slate-800 dark:bg-slate-900 dark:text-white dark:border-slate-700"
                        />
                    </div>

                    <div class="flex justify-between items-center text-slate-500 pt-1">
                        <span>Pajak PPN (Rp)</span>
                        <input
                            v-model.number="taxInput"
                            type="number"
                            min="0"
                            class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-right text-xs font-bold text-slate-800 dark:bg-slate-900 dark:text-white dark:border-slate-700"
                        />
                    </div>

                    <div class="flex justify-between items-center border-t border-slate-200 pt-2 dark:border-slate-800">
                        <span class="text-sm font-black text-slate-900 dark:text-white">TOTAL BAYAR</span>
                        <span class="text-lg font-black text-blue-600 dark:text-blue-400">{{ formatRupiah(grandTotal) }}</span>
                    </div>
                </div>

                <div class="flex gap-2 pt-1">
                    <button
                        @click="clearCart"
                        :disabled="cart.length === 0"
                        class="flex items-center justify-center rounded-xl border border-slate-200 px-3 py-3 text-slate-500 hover:bg-slate-100 disabled:opacity-40 dark:border-slate-800"
                    >
                        <RotateCcw class="h-4 w-4" />
                    </button>
                    <button
                        @click="openPaymentModal"
                        :disabled="cart.length === 0 || hasStockError"
                        class="flex-1 flex items-center justify-center gap-2 rounded-xl bg-blue-600 hover:bg-blue-700 py-3 text-sm font-extrabold text-white shadow-lg shadow-blue-600/30 transition-all disabled:opacity-40"
                    >
                        <CreditCard class="h-4 w-4" />
                        Bayar Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- PAYMENT MODAL -->
    <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <Receipt class="h-5 w-5 text-blue-600" /> Selesaikan Pembayaran
                </h3>
                <button @click="showPaymentModal = false" class="text-slate-400 hover:text-slate-600">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Total Banner -->
            <div class="rounded-xl bg-blue-50 p-4 text-center dark:bg-blue-950/60 border border-blue-100 dark:border-blue-900">
                <span class="text-xs text-blue-600 dark:text-blue-400 font-semibold">Total Yang Harus Dibayar</span>
                <p class="text-3xl font-black text-blue-600 dark:text-blue-400">{{ formatRupiah(grandTotal) }}</p>
            </div>

            <!-- Payment Method Selection -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-2">Metode Pembayaran</label>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        @click="selectedPaymentMethod = 'cash'"
                        class="flex items-center justify-center gap-2 p-3 rounded-xl border text-xs font-bold transition-all"
                        :class="selectedPaymentMethod === 'cash' ? 'border-blue-600 bg-blue-50 text-blue-600 dark:bg-blue-950' : 'border-slate-200 text-slate-600 dark:border-slate-800'"
                    >
                        <CreditCard class="h-4 w-4" /> Tunai / Cash
                    </button>
                    <button
                        @click="selectedPaymentMethod = 'qris'; isQrisVerified = false; paidAmountInput = grandTotal"
                        class="flex items-center justify-center gap-2 p-3 rounded-xl border text-xs font-bold transition-all"
                        :class="selectedPaymentMethod === 'qris' ? 'border-blue-600 bg-blue-50 text-blue-600 dark:bg-blue-950' : 'border-slate-200 text-slate-600 dark:border-slate-800'"
                    >
                        <QrCode class="h-4 w-4" /> QRIS
                    </button>
                </div>
            </div>

            <!-- Paid Amount & Quick Buttons (if Cash) -->
            <div v-if="selectedPaymentMethod === 'cash'" class="space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Uang Diterima (Rp)</label>
                    <input
                        v-model.number="paidAmountInput"
                        type="number"
                        min="0"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-right text-lg font-black text-slate-900 focus:border-blue-500 focus:outline-none dark:bg-slate-900 dark:text-white dark:border-slate-800"
                    />
                </div>

                <!-- Quick Money Pills -->
                <div class="flex flex-wrap gap-1.5">
                    <button
                        v-for="amt in quickMoneyOptions"
                        :key="amt"
                        @click="setQuickMoney(amt)"
                        class="rounded-lg bg-slate-100 hover:bg-blue-100 hover:text-blue-700 px-2.5 py-1 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                    >
                        {{ formatRupiah(amt) }}
                    </button>
                </div>

                <!-- Change Display -->
                <div class="flex justify-between items-center rounded-xl bg-emerald-50 p-3 text-xs font-bold text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200">
                    <span>Uang Kembalian:</span>
                    <span class="text-lg font-black">{{ formatRupiah(changeAmount) }}</span>
                </div>
            </div>

            <!-- QRIS Verification Section -->
            <div v-if="selectedPaymentMethod === 'qris'" class="space-y-3">
                <div class="rounded-xl bg-blue-50 p-3 text-center dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900 space-y-1">
                    <div class="flex items-center justify-center gap-1.5 font-bold text-xs text-blue-700 dark:text-blue-300">
                        <QrCode class="h-4 w-4" /> Pembayaran QRIS Scan Kode
                    </div>
                    <p class="text-[11px] text-blue-600 dark:text-blue-400">Silakan terima pembayaran QRIS sebesar <strong class="font-extrabold">{{ formatRupiah(grandTotal) }}</strong>.</p>
                </div>

                <label class="flex items-start gap-2.5 p-3 rounded-xl border border-blue-200 bg-blue-50/60 cursor-pointer dark:bg-slate-800 dark:border-slate-700 select-none">
                    <input
                        v-model="isQrisVerified"
                        type="checkbox"
                        class="mt-0.5 rounded text-blue-600 focus:ring-blue-500 h-4 w-4"
                    />
                    <span class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                        <strong>Verifikasi Pembayaran:</strong> Saya mengonfirmasi bahwa pembayaran QRIS sebesar <strong>{{ formatRupiah(grandTotal) }}</strong> telah berhasil diterima.
                    </span>
                </label>
            </div>

            <div class="flex gap-2 pt-2">
                <button
                    @click="showPaymentModal = false"
                    class="w-1/3 rounded-xl border border-slate-200 py-3 text-xs font-bold text-slate-600 hover:bg-slate-100 dark:border-slate-800 dark:text-slate-400"
                >
                    Batal
                </button>
                <button
                    @click="processCheckout"
                    :disabled="form.processing || (selectedPaymentMethod === 'cash' && paidAmountInput < grandTotal) || (selectedPaymentMethod === 'qris' && !isQrisVerified)"
                    class="w-2/3 flex items-center justify-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 py-3 text-xs font-extrabold text-white shadow-lg shadow-emerald-600/30 transition-all disabled:opacity-40"
                >
                    <CheckCircle2 class="h-4 w-4" /> Cetak & Selesaikan
                </button>
            </div>
        </div>
    </div>

    <!-- PRINTABLE RECEIPT MODAL -->
    <div v-if="showReceiptModal && completedTransaction" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-2xl dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4 max-h-[90vh] overflow-y-auto">
            <!-- Success Notification Alert Banner -->
            <div class="flex items-center gap-3 rounded-xl bg-emerald-50 p-3 text-xs font-semibold text-emerald-900 dark:bg-emerald-950/80 dark:text-emerald-200 border border-emerald-200 dark:border-emerald-800">
                <CheckCircle2 class="h-5 w-5 text-emerald-600 dark:text-emerald-400 shrink-0" />
                <div>
                    <p class="font-bold text-xs text-emerald-800 dark:text-emerald-300">Pembayaran Berhasil Diverifikasi!</p>
                    <p class="text-[11px] text-emerald-700 dark:text-emerald-400">Transaksi diselesaikan & tercatat.</p>
                </div>
            </div>

            <!-- Printable Struk thermal 80mm area -->
            <div id="printable-receipt" class="font-mono text-xs text-slate-800 space-y-3 p-4 border border-dashed border-slate-300 rounded-xl bg-slate-50 dark:bg-slate-950 dark:text-slate-200 dark:border-slate-800 shadow-inner">
                <!-- Header -->
                <div class="text-center border-b-2 border-dashed border-slate-300 dark:border-slate-700 pb-3 space-y-0.5">
                    <h2 class="font-black text-base tracking-wider text-slate-900 dark:text-white uppercase">TOKO ADE FITRI</h2>
                    <p class="text-[10px] font-bold text-slate-600 dark:text-slate-400">Toko Bahan Kue & Plastik (Grosir & Eceran)</p>
                    <p class="text-[9px] text-slate-500 dark:text-slate-400">Pasar Karangampel Blok D4-D6 • WA: 082315580021</p>
                </div>

                <!-- Transaction Info Grid -->
                <div class="text-[10px] space-y-1 border-b border-dashed border-slate-300 dark:border-slate-700 pb-2.5">
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">No. Invoice</span>
                        <span class="font-bold font-mono text-slate-900 dark:text-white">{{ completedTransaction.invoice_number }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Waktu</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-300">{{ new Date(completedTransaction.created_at).toLocaleString('id-ID', { dateStyle: 'short', timeStyle: 'medium' }) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Kasir</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-300">{{ completedTransaction.user?.name || 'Kasir' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 dark:text-slate-400">Pelanggan</span>
                        <span class="font-semibold text-slate-800 dark:text-slate-300">{{ completedTransaction.customer?.name || 'Pelanggan Umum' }}</span>
                    </div>
                </div>

                <!-- Items list -->
                <div class="space-y-2 border-b-2 border-dashed border-slate-300 dark:border-slate-700 pb-3 text-[11px]">
                    <div v-for="item in completedTransaction.items" :key="item.id" class="space-y-0.5">
                        <p class="font-bold text-slate-900 dark:text-white leading-tight">{{ item.product?.name || 'Produk' }}</p>
                        <div class="flex justify-between text-[10px] text-slate-600 dark:text-slate-400">
                            <span>
                                {{ item.quantity }} x {{ formatRupiah(item.unit_price) }}
                                <span class="text-[9px] font-bold uppercase px-1 rounded bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 ml-1">
                                    {{ item.price_type === 'wholesale' ? 'Grosir' : 'Eceran' }}
                                </span>
                            </span>
                            <span class="font-bold text-slate-900 dark:text-white font-mono">{{ formatRupiah(item.subtotal) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Financial Totals -->
                <div class="space-y-1 text-[11px] border-b-2 border-dashed border-slate-300 dark:border-slate-700 pb-2.5">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Subtotal</span>
                        <span class="font-mono">{{ formatRupiah(completedTransaction.total_amount) }}</span>
                    </div>
                    <div v-if="completedTransaction.discount_amount > 0" class="flex justify-between text-red-600 dark:text-red-400">
                        <span>Potongan / Diskon</span>
                        <span class="font-mono">-{{ formatRupiah(completedTransaction.discount_amount) }}</span>
                    </div>
                    <div v-if="completedTransaction.tax_amount > 0" class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Pajak (PPN)</span>
                        <span class="font-mono">+{{ formatRupiah(completedTransaction.tax_amount) }}</span>
                    </div>
                    <div class="flex justify-between font-black text-xs pt-1.5 border-t border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white">
                        <span>TOTAL BAYAR</span>
                        <span class="text-sm font-black text-blue-600 dark:text-blue-400 font-mono">{{ formatRupiah(completedTransaction.grand_total) }}</span>
                    </div>
                    <div class="flex justify-between text-[10px] text-slate-600 dark:text-slate-400 pt-1">
                        <span>Metode Bayar ({{ completedTransaction.payment_method.toUpperCase() }})</span>
                        <span class="font-mono font-bold">{{ formatRupiah(completedTransaction.paid_amount) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-[11px] text-emerald-600 dark:text-emerald-400">
                        <span>Kembali</span>
                        <span class="font-mono font-black text-xs">{{ formatRupiah(completedTransaction.change_amount) }}</span>
                    </div>
                </div>

                <!-- Footer Message -->
                <div class="text-center text-[9px] text-slate-500 dark:text-slate-400 pt-1 space-y-0.5">
                    <p class="font-bold tracking-wider text-slate-700 dark:text-slate-300">*** TERIMA KASIH ***</p>
                    <p>Barang yang sudah dibeli tidak dapat ditukar/dikembalikan</p>
                    <p class="text-[8px] text-slate-400">Simpan struk ini sebagai bukti transaksi sah</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-2 pt-1">
                <button
                    @click="closeReceiptModal"
                    class="flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 py-3 text-xs font-bold text-slate-700 hover:bg-slate-100 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800 transition-all cursor-pointer select-none"
                >
                    <CheckCircle2 class="h-4 w-4 text-slate-500" /> Selesai & Buka Kasir
                </button>
                <button
                    @click="printReceipt"
                    class="flex items-center justify-center gap-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 py-3 text-xs font-extrabold text-white shadow-lg shadow-blue-600/30 transition-all cursor-pointer select-none"
                >
                    <Printer class="h-4 w-4" /> Cetak Struk & Selesai
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    @page {
        size: 80mm auto;
        margin: 2mm 4mm;
    }
    body * {
        visibility: hidden !important;
    }
    #printable-receipt,
    #printable-receipt * {
        visibility: visible !important;
    }
    #printable-receipt {
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        width: 72mm !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        background: white !important;
        color: black !important;
        box-shadow: none !important;
        font-family: 'Courier New', Courier, monospace !important;
        font-size: 11px !important;
        line-height: 1.3 !important;
    }
    #printable-receipt h2,
    #printable-receipt p,
    #printable-receipt span,
    #printable-receipt div {
        color: black !important;
        background: transparent !important;
    }
}
</style>
