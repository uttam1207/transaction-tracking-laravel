<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\AnimalAction;
use App\Models\BreedingRecord;
use App\Models\ComplianceDocument;
use App\Models\CrmCustomer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\FarmRecord;
use App\Models\Franchise;
use App\Models\HealthRecord;
use App\Models\MachineMaintenance;
use App\Models\MilkEntry;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ASDairyMasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Run Base ASDairy Seeder for Stock and Feed
        $this->call(ASDairySeeder::class);

        $admin = User::first();

        // 2. Animals (10 Buffaloes + 5 Calves)
        $animalData = [
            ['tag_number' => 'ASD-BUF-001', 'name' => 'Ganga', 'breed' => 'Murrah', 'lactation_number' => 2, 'pregnancy_status' => 'Pregnant', 'current_weight' => 520],
            ['tag_number' => 'ASD-BUF-002', 'name' => 'Yamuna', 'breed' => 'Murrah', 'lactation_number' => 3, 'pregnancy_status' => 'Open', 'current_weight' => 540],
            ['tag_number' => 'ASD-BUF-003', 'name' => 'Saraswati', 'breed' => 'Murrah', 'lactation_number' => 1, 'pregnancy_status' => 'Pregnant', 'current_weight' => 480],
            ['tag_number' => 'ASD-BUF-004', 'name' => 'Kaveri', 'breed' => 'Murrah', 'lactation_number' => 4, 'pregnancy_status' => 'Dry', 'current_weight' => 560],
            ['tag_number' => 'ASD-BUF-005', 'name' => 'Narmada', 'breed' => 'Bhadawari', 'lactation_number' => 2, 'pregnancy_status' => 'Inseminated', 'current_weight' => 490],
            ['tag_number' => 'ASD-BUF-006', 'name' => 'Godavari', 'breed' => 'Murrah', 'lactation_number' => 3, 'pregnancy_status' => 'Pregnant', 'current_weight' => 530],
            ['tag_number' => 'ASD-BUF-007', 'name' => 'Krishna', 'breed' => 'Murrah', 'lactation_number' => 2, 'pregnancy_status' => 'Dry', 'current_weight' => 510],
            ['tag_number' => 'ASD-BUF-008', 'name' => 'Tapti', 'breed' => 'Murrah', 'lactation_number' => 1, 'pregnancy_status' => 'Pregnant', 'current_weight' => 470],
            ['tag_number' => 'ASD-BUF-009', 'name' => 'Mahanadi', 'breed' => 'Murrah', 'lactation_number' => 3, 'pregnancy_status' => 'Open', 'current_weight' => 550],
            ['tag_number' => 'ASD-BUF-010', 'name' => 'Sarayu', 'breed' => 'Murrah', 'lactation_number' => 2, 'pregnancy_status' => 'Dry', 'current_weight' => 500],
            ['tag_number' => 'ASD-CLF-001', 'name' => 'Calf Chhoti', 'breed' => 'Murrah', 'lactation_number' => 0, 'pregnancy_status' => 'Open', 'current_weight' => 85],
            ['tag_number' => 'ASD-CLF-002', 'name' => 'Calf Golu', 'breed' => 'Murrah', 'lactation_number' => 0, 'pregnancy_status' => 'Open', 'current_weight' => 90],
        ];

        foreach ($animalData as $ad) {
            $animal = Animal::firstOrCreate(['tag_number' => $ad['tag_number']], $ad);

            // Milk Entry if milking
            if ($animal->pregnancy_status !== 'Dry' && $animal->lactation_number > 0) {
                MilkEntry::firstOrCreate(
                    ['animal_id' => $animal->id, 'date' => now()->toDateString(), 'shift' => 'Morning'],
                    [
                        'quantity_liters' => rand(6, 10),
                        'fat_percentage' => 7.8,
                        'snf_percentage' => 9.1,
                        'quality_rating' => 'Grade A',
                        'recorded_by' => $admin->id,
                    ]
                );
                MilkEntry::firstOrCreate(
                    ['animal_id' => $animal->id, 'date' => now()->toDateString(), 'shift' => 'Evening'],
                    [
                        'quantity_liters' => rand(5, 8),
                        'fat_percentage' => 7.6,
                        'snf_percentage' => 9.0,
                        'quality_rating' => 'Grade A',
                        'recorded_by' => $admin->id,
                    ]
                );
            }

            // Health Record
            HealthRecord::firstOrCreate(
                ['animal_id' => $animal->id, 'record_type' => 'Vaccination'],
                [
                    'date' => now()->subDays(15)->toDateString(),
                    'disease_symptoms' => 'Routine FMD Vaccination',
                    'treatment_given' => 'FMD Vaccine Administered',
                    'vet_doctor_name' => 'Dr. Verma',
                    'cost' => 150.00,
                    'status' => 'Completed',
                ]
            );

            // Breeding Record
            BreedingRecord::firstOrCreate(
                ['animal_id' => $animal->id],
                [
                    'heat_date' => now()->subDays(60)->toDateString(),
                    'ai_date' => now()->subDays(59)->toDateString(),
                    'bull_semen_code' => 'SEM-MUR-884',
                    'pregnancy_check_date' => now()->subDays(30)->toDateString(),
                    'is_pregnant' => true,
                    'expected_calving_date' => now()->addDays(240)->toDateString(),
                    'status' => 'Confirmed Pregnant',
                ]
            );
        }

        // 3. Farm Records (Napier Plantation)
        FarmRecord::firstOrCreate(
            ['plot_name' => 'Plot A - Anoo Village'],
            [
                'crop_type' => 'Napier Hybrid Grass',
                'plantation_date' => now()->subMonths(6)->toDateString(),
                'fertilizer_used' => 'Organic Cow Dung Compost',
                'harvest_date' => now()->subDays(5)->toDateString(),
                'yield_kg' => 4500.00,
                'diesel_liters' => 25.00,
                'electricity_units' => 120.00,
                'water_usage_liters' => 15000.00,
            ]
        );

        // 4. CRM Customers
        $customers = [
            ['name' => 'Damoh Milk Collection Dairy', 'category' => 'Milk Buyer', 'phone' => '+91 98765 43210', 'total_business_value' => 150000],
            ['name' => 'Shri Ram Traders', 'category' => 'Animal Buyer', 'phone' => '+91 98765 11223', 'total_business_value' => 85000],
            ['name' => 'Ramesh Franchise Lead', 'category' => 'Franchise Lead', 'phone' => '+91 94251 00000', 'total_business_value' => 0],
            ['name' => 'Anoo Village Co-Op', 'category' => 'Milk Buyer', 'phone' => '+91 91111 22233', 'total_business_value' => 95000],
        ];

        foreach ($customers as $c) {
            $customer = CrmCustomer::firstOrCreate(['name' => $c['name']], $c);

            // Create Sales Invoice
            SalesOrder::firstOrCreate(
                ['invoice_number' => 'INV-2026-' . rand(100, 999)],
                [
                    'crm_customer_id' => $customer->id,
                    'item_type' => 'Milk Sales',
                    'sale_date' => now()->toDateString(),
                    'quantity' => 140,
                    'rate' => 60.00,
                    'total_amount' => 8400.00,
                    'payment_status' => 'Paid',
                ]
            );
        }

        // 5. Franchises
        Franchise::firstOrCreate(
            ['franchise_code' => 'ASD-FRAN-01'],
            [
                'owner_name' => 'Rajesh Sharma',
                'location' => 'Damoh City Outlet 1',
                'contact_number' => '+91 98260 12345',
                'agreement_date' => now()->subMonths(3)->toDateString(),
                'investment_amount' => 500000.00,
                'status' => 'Active',
                'royalty_percentage' => 5.0,
                'milk_collected_liters' => 12500.00,
            ]
        );

        // 6. Procurement & Vendor
        $vendor = Vendor::firstOrCreate(
            ['name' => 'Bundelkhand Cattle Feed Suppliers'],
            ['contact_person' => 'Sanjay Patel', 'phone' => '+91 97555 44332', 'category' => 'Feed & Fodder']
        );

        PurchaseOrder::firstOrCreate(
            ['po_number' => 'PO-2026-001'],
            [
                'vendor_id' => $vendor->id,
                'order_date' => now()->subDays(10)->toDateString(),
                'total_amount' => 25000.00,
                'status' => 'Paid',
                'remarks' => 'Concentrate feed 50 bags delivered',
            ]
        );

        // 7. Machinery Maintenance
        MachineMaintenance::firstOrCreate(
            ['machine_name' => 'DeLaval Milking Machine 2-Bucket'],
            [
                'maintenance_type' => 'Preventive Schedule',
                'service_date' => now()->subDays(20)->toDateString(),
                'cost' => 1200.00,
                'serviced_by' => 'DeLaval Service MP',
                'description' => 'Vacuum pump check and teat cup liner replacement',
                'next_service_due' => now()->addDays(70)->toDateString(),
            ]
        );

        // 8. Compliance Documents
        ComplianceDocument::firstOrCreate(
            ['document_title' => 'FSSAI Dairy License Anoo Village'],
            [
                'category' => 'FSSAI',
                'document_number' => 'FSSAI-11826000192',
                'issue_date' => now()->subYear()->toDateString(),
                'expiry_date' => now()->addMonths(6)->toDateString(),
                'status' => 'Active',
            ]
        );
    }
}
