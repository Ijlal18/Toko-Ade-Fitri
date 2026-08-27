<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $ownerRole = Role::firstOrCreate(['name' => 'Owner']);
        $kasirRole = Role::firstOrCreate(['name' => 'Karyawan']);

        // 2. Default Users
        $admin = User::updateOrCreate(
            ['email' => 'admin@tokoadefitri.com'],
            [
                'name' => 'Admin Toko',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole($adminRole);

        $owner = User::updateOrCreate(
            ['email' => 'owner@tokoadefitri.com'],
            [
                'name' => 'Ade Fitri (Owner)',
                'password' => Hash::make('password'),
            ]
        );
        $owner->assignRole($ownerRole);

        $kasir = User::updateOrCreate(
            ['email' => 'kasir@tokoadefitri.com'],
            [
                'name' => 'Siti Kasir',
                'password' => Hash::make('password'),
            ]
        );
        $kasir->assignRole($kasirRole);

        // 3. Categories (Bahan Kue & Plastik)
        $catBahanKue = Category::create(['name' => 'Bahan Kue', 'slug' => 'bahan-kue', 'description' => 'Tepung, pengembang, mentega, cokelat, dll.']);
        $catPlastik = Category::create(['name' => 'Plastik & Kemasan', 'slug' => 'plastik-kemasan', 'description' => 'Kantong plastik, mika, box makanan, standing pouch']);
        $catSembako = Category::create(['name' => 'Sembako & Minuman', 'slug' => 'sembako-minuman', 'description' => 'Gula, minyak goreng, susu, sirup']);
        $catPeralatan = Category::create(['name' => 'Peralatan Kue', 'slug' => 'peralatan-kue', 'description' => 'Cetakan kue, spuit, kertas roti, loyang']);

        // 4. Suppliers
        $supBogasari = Supplier::create([
            'name' => 'PT Indofood Sukses Makmur (Bogasari)',
            'phone' => '021-5551234',
            'email' => 'sales@bogasari.com',
            'address' => 'Jakarta Utara',
            'notes' => 'Supplier Tepung Terigu Segitiga Biru & Cakra Kembar',
        ]);
        $supPlastikJaya = Supplier::create([
            'name' => 'CV Plastik Jaya Makmur',
            'phone' => '021-8889999',
            'email' => 'order@plastikjaya.com',
            'address' => 'Tangerang',
            'notes' => 'Supplier kantong HDPE, mika, & box bakery',
        ]);
        $supAnchor = Supplier::create([
            'name' => 'PT Fonterra (Anchor Butter)',
            'phone' => '021-7772222',
            'email' => 'supply@fonterra.com',
            'address' => 'Jakarta Selatan',
            'notes' => 'Supplier Mentega & Keju Premium',
        ]);

        // 5. Products (Bahan Kue & Plastik)
        $productsData = [
            [
                'category_id' => $catBahanKue->id,
                'supplier_id' => $supBogasari->id,
                'sku' => 'PRD-001',
                'barcode' => '899100100101',
                'name' => 'Tepung Terigu Segitiga Biru 1 KG',
                'purchase_price' => 10500,
                'selling_price_retail' => 13000,
                'selling_price_wholesale' => 11800,
                'stock' => 120,
                'min_stock' => 20,
                'unit' => 'kg',
            ],
            [
                'category_id' => $catBahanKue->id,
                'supplier_id' => $supBogasari->id,
                'sku' => 'PRD-002',
                'barcode' => '899100100102',
                'name' => 'Tepung Terigu Cakra Kembar 1 KG',
                'purchase_price' => 11500,
                'selling_price_retail' => 14000,
                'selling_price_wholesale' => 12800,
                'stock' => 85,
                'min_stock' => 15,
                'unit' => 'kg',
            ],
            [
                'category_id' => $catBahanKue->id,
                'supplier_id' => $supAnchor->id,
                'sku' => 'PRD-003',
                'barcode' => '899100100103',
                'name' => 'Mentega Anchor Unsalted 227gr',
                'purchase_price' => 42000,
                'selling_price_retail' => 49000,
                'selling_price_wholesale' => 45500,
                'stock' => 40,
                'min_stock' => 10,
                'unit' => 'pcs',
            ],
            [
                'category_id' => $catBahanKue->id,
                'supplier_id' => $supAnchor->id,
                'sku' => 'PRD-004',
                'barcode' => '899100100104',
                'name' => 'Cokelat Colatta Dark Compound 1 KG',
                'purchase_price' => 48000,
                'selling_price_retail' => 58000,
                'selling_price_wholesale' => 53000,
                'stock' => 50,
                'min_stock' => 10,
                'unit' => 'pcs',
            ],
            [
                'category_id' => $catPlastik->id,
                'supplier_id' => $supPlastikJaya->id,
                'sku' => 'PRD-005',
                'barcode' => '899200100201',
                'name' => 'Kantong Plastik HD Bening 15x30 (Pack 500gr)',
                'purchase_price' => 14000,
                'selling_price_retail' => 18000,
                'selling_price_wholesale' => 15800,
                'stock' => 200,
                'min_stock' => 30,
                'unit' => 'pack',
            ],
            [
                'category_id' => $catPlastik->id,
                'supplier_id' => $supPlastikJaya->id,
                'sku' => 'PRD-006',
                'barcode' => '899200100202',
                'name' => 'Mika Kue Square Transparan 7x7 (Isi 100)',
                'purchase_price' => 22000,
                'selling_price_retail' => 28000,
                'selling_price_wholesale' => 24500,
                'stock' => 90,
                'min_stock' => 15,
                'unit' => 'pack',
            ],
            [
                'category_id' => $catPlastik->id,
                'supplier_id' => $supPlastikJaya->id,
                'sku' => 'PRD-007',
                'barcode' => '899200100203',
                'name' => 'Standing Pouch Ziplock 14x22 Bening (Isi 50)',
                'purchase_price' => 28000,
                'selling_price_retail' => 35000,
                'selling_price_wholesale' => 31000,
                'stock' => 8, // Stok menipis untuk demo alert
                'min_stock' => 15,
                'unit' => 'pack',
            ],
            [
                'category_id' => $catSembako->id,
                'supplier_id' => $supBogasari->id,
                'sku' => 'PRD-008',
                'barcode' => '899300100301',
                'name' => 'Gula Pasir Kristal Putih 1 KG',
                'purchase_price' => 15000,
                'selling_price_retail' => 18000,
                'selling_price_wholesale' => 16500,
                'stock' => 150,
                'min_stock' => 25,
                'unit' => 'kg',
            ],
            [
                'category_id' => $catPeralatan->id,
                'supplier_id' => $supPlastikJaya->id,
                'sku' => 'PRD-009',
                'barcode' => '899400100401',
                'name' => 'Kertas Roti Baking Paper Roll 30cm x 10m',
                'purchase_price' => 16000,
                'selling_price_retail' => 22000,
                'selling_price_wholesale' => 19000,
                'stock' => 3, // Low stock demo alert
                'min_stock' => 10,
                'unit' => 'roll',
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $p) {
            $product = Product::create($p);
            $createdProducts[] = $product;

            // Log initial stock movement
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => $admin->id,
                'type' => 'in',
                'quantity' => $product->stock,
                'reason' => 'Stok awal inventaris toko',
                'reference' => 'PO-INIT-001',
            ]);
        }

        // 6. Customers
        $custUmum = Customer::create([
            'name' => 'Pelanggan Umum (Eceran)',
            'phone' => '-',
            'address' => '-',
            'type' => 'retail',
            'points' => 0,
        ]);
        $custBudi = Customer::create([
            'name' => 'Budi Santoso (Bakery Rose)',
            'phone' => '081234567890',
            'address' => 'Jl. Merdeka No. 45',
            'type' => 'wholesale',
            'points' => 150,
        ]);
        $custDewi = Customer::create([
            'name' => 'Dewi Cake & Catering',
            'phone' => '081987654321',
            'address' => 'Jl. Mawar No. 12',
            'type' => 'wholesale',
            'points' => 80,
        ]);

        // 7. Initial Demo Transactions
        $inv1 = 'INV-'.date('Ymd').'-0001';
        $t1 = Transaction::create([
            'invoice_number' => $inv1,
            'user_id' => $kasir->id,
            'customer_id' => $custBudi->id,
            'total_amount' => 147600,
            'discount_amount' => 5000,
            'tax_amount' => 0,
            'grand_total' => 142600,
            'paid_amount' => 150000,
            'change_amount' => 7400,
            'payment_method' => 'cash',
            'status' => 'completed',
            'notes' => 'Pembelian grosir bahan kue',
        ]);

        TransactionItem::create([
            'transaction_id' => $t1->id,
            'product_id' => $createdProducts[0]->id, // Tepung Segitiga
            'price_type' => 'wholesale',
            'purchase_price' => $createdProducts[0]->purchase_price,
            'unit_price' => 11800,
            'quantity' => 10,
            'subtotal' => 118000,
        ]);

        TransactionItem::create([
            'transaction_id' => $t1->id,
            'product_id' => $createdProducts[4]->id, // Plastik HD
            'price_type' => 'wholesale',
            'purchase_price' => $createdProducts[4]->purchase_price,
            'unit_price' => 14800,
            'quantity' => 2,
            'subtotal' => 29600,
        ]);

        // Deduct stock for transaction 1
        $createdProducts[0]->decrement('stock', 10);
        $createdProducts[4]->decrement('stock', 2);
    }
}
