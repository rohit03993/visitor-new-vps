<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\VmsUser;
use App\Models\Branch;
use App\Models\Address;
use App\Models\Visitor;
use App\Models\InteractionHistory;
use App\Models\Remark;
use App\Models\UserBranchPermission;

class IndianDemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Branches
        $branches = [
            ['branch_name' => 'Mumbai Central', 'branch_code' => 'MUM001', 'address' => 'Andheri West, Mumbai, Maharashtra'],
            ['branch_name' => 'Delhi North', 'branch_code' => 'DEL001', 'address' => 'Connaught Place, New Delhi'],
            ['branch_name' => 'Bangalore Tech', 'branch_code' => 'BLR001', 'address' => 'Electronic City, Bangalore, Karnataka'],
            ['branch_name' => 'Chennai South', 'branch_code' => 'CHN001', 'address' => 'T. Nagar, Chennai, Tamil Nadu'],
            ['branch_name' => 'Kolkata East', 'branch_code' => 'KOL001', 'address' => 'Salt Lake, Kolkata, West Bengal'],
        ];

        foreach ($branches as $branchData) {
            Branch::create($branchData);
        }

        // 2. Create Addresses (Meeting Locations)
        $addresses = [
            ['address_name' => 'Conference Room A', 'full_address' => 'Ground Floor, Conference Room A, Mumbai Central Branch'],
            ['address_name' => 'Meeting Hall B', 'full_address' => 'First Floor, Meeting Hall B, Delhi North Branch'],
            ['address_name' => 'Tech Lab', 'full_address' => 'Second Floor, Technology Lab, Bangalore Tech Branch'],
            ['address_name' => 'Board Room', 'full_address' => 'Third Floor, Board Room, Chennai South Branch'],
            ['address_name' => 'Training Center', 'full_address' => 'Ground Floor, Training Center, Kolkata East Branch'],
            ['address_name' => 'Reception Area', 'full_address' => 'Ground Floor, Main Reception, All Branches'],
            ['address_name' => 'Cafeteria', 'full_address' => 'Ground Floor, Employee Cafeteria, All Branches'],
            ['address_name' => 'Parking Area', 'full_address' => 'Basement, Visitor Parking, All Branches'],
        ];

        foreach ($addresses as $addressData) {
            Address::create($addressData);
        }

        // 3. Create Users
        $users = [
            // Admin
            [
                'name' => 'Rajesh Kumar',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'branch_id' => 1,
                'mobile_number' => '9876543210',
                'can_view_remarks' => true,
                'can_download_excel' => true,
                'is_active' => true,
            ],
            // Staff Users
            [
                'name' => 'Priya Sharma',
                'username' => 'priya',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'branch_id' => 1,
                'mobile_number' => '9876543211',
                'can_view_remarks' => true,
                'can_download_excel' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Amit Singh',
                'username' => 'amit',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'branch_id' => 2,
                'mobile_number' => '9876543212',
                'can_view_remarks' => true,
                'can_download_excel' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Sneha Patel',
                'username' => 'sneha',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'branch_id' => 3,
                'mobile_number' => '9876543213',
                'can_view_remarks' => false,
                'can_download_excel' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Vikram Reddy',
                'username' => 'vikram',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'branch_id' => 4,
                'mobile_number' => '9876543214',
                'can_view_remarks' => true,
                'can_download_excel' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Anita Das',
                'username' => 'anita',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'branch_id' => 5,
                'mobile_number' => '9876543215',
                'can_view_remarks' => false,
                'can_download_excel' => false,
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            VmsUser::create($userData);
        }

        // 4. Create User Branch Permissions
        $permissions = [
            // Priya can view remarks for Mumbai and Delhi
            ['user_id' => 2, 'branch_id' => 1, 'can_view_remarks' => true, 'can_download_excel' => false],
            ['user_id' => 2, 'branch_id' => 2, 'can_view_remarks' => true, 'can_download_excel' => false],
            
            // Amit can view remarks for Delhi and Bangalore
            ['user_id' => 3, 'branch_id' => 2, 'can_view_remarks' => true, 'can_download_excel' => false],
            ['user_id' => 3, 'branch_id' => 3, 'can_view_remarks' => true, 'can_download_excel' => false],
            
            // Vikram can view remarks for Chennai and download Excel
            ['user_id' => 5, 'branch_id' => 4, 'can_view_remarks' => true, 'can_download_excel' => true],
            ['user_id' => 5, 'branch_id' => 5, 'can_view_remarks' => true, 'can_download_excel' => true],
        ];

        foreach ($permissions as $permissionData) {
            UserBranchPermission::create($permissionData);
        }

        // 5. Create Indian Visitors
        $visitors = [
            ['mobile_number' => '+919876543210', 'name' => 'Arjun Mehta', 'last_updated_by' => 2],
            ['mobile_number' => '+919876543211', 'name' => 'Kavya Iyer', 'last_updated_by' => 2],
            ['mobile_number' => '+919876543212', 'name' => 'Rohit Gupta', 'last_updated_by' => 3],
            ['mobile_number' => '+919876543213', 'name' => 'Priyanka Joshi', 'last_updated_by' => 3],
            ['mobile_number' => '+919876543214', 'name' => 'Suresh Kumar', 'last_updated_by' => 4],
            ['mobile_number' => '+919876543215', 'name' => 'Meera Nair', 'last_updated_by' => 4],
            ['mobile_number' => '+919876543216', 'name' => 'Deepak Sharma', 'last_updated_by' => 5],
            ['mobile_number' => '+919876543217', 'name' => 'Sunita Agarwal', 'last_updated_by' => 5],
            ['mobile_number' => '+919876543218', 'name' => 'Rajesh Verma', 'last_updated_by' => 6],
            ['mobile_number' => '+919876543219', 'name' => 'Pooja Singh', 'last_updated_by' => 6],
        ];

        foreach ($visitors as $visitorData) {
            Visitor::create($visitorData);
        }

        // 6. Create Interaction History (Visits) - More comprehensive data
        $interactions = [
            // Recent interactions - Mumbai Branch (Priya)
            [
                'visitor_id' => 1, 'name_entered' => 'Arjun Mehta', 'mobile_number' => '+919876543210',
                'purpose' => 'Job Interview - Software Developer Position', 'meeting_with' => 2, 'address_id' => 1,
                'mode' => 'In-Campus', 'created_by' => 2, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)
            ],
            [
                'visitor_id' => 2, 'name_entered' => 'Kavya Iyer', 'mobile_number' => '+919876543211',
                'purpose' => 'Client Meeting - Project Discussion', 'meeting_with' => 2, 'address_id' => 2,
                'mode' => 'In-Campus', 'created_by' => 2, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(4), 'updated_at' => now()->subHours(4)
            ],
            [
                'visitor_id' => 1, 'name_entered' => 'Arjun Mehta', 'mobile_number' => '+919876543210',
                'purpose' => 'Follow-up Interview - Technical Round', 'meeting_with' => 2, 'address_id' => 1,
                'mode' => 'In-Campus', 'created_by' => 2, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(1), 'updated_at' => now()->subHours(1)
            ],

            // Delhi Branch (Amit)
            [
                'visitor_id' => 3, 'name_entered' => 'Rohit Gupta', 'mobile_number' => '+919876543212',
                'purpose' => 'Vendor Meeting - Supply Chain Discussion', 'meeting_with' => 3, 'address_id' => 3,
                'mode' => 'In-Campus', 'created_by' => 3, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(6), 'updated_at' => now()->subHours(6)
            ],
            [
                'visitor_id' => 4, 'name_entered' => 'Priyanka Joshi', 'mobile_number' => '+919876543213',
                'purpose' => 'Training Session - New Employee Orientation', 'meeting_with' => 3, 'address_id' => 4,
                'mode' => 'In-Campus', 'created_by' => 3, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(8), 'updated_at' => now()->subHours(8)
            ],
            [
                'visitor_id' => 3, 'name_entered' => 'Rohit Gupta', 'mobile_number' => '+919876543212',
                'purpose' => 'Contract Renewal Discussion', 'meeting_with' => 3, 'address_id' => 3,
                'mode' => 'Telephonic', 'created_by' => 3, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(5), 'updated_at' => now()->subHours(5)
            ],

            // Bangalore Branch (Sneha - No remark permissions)
            [
                'visitor_id' => 5, 'name_entered' => 'Suresh Kumar', 'mobile_number' => '+919876543214',
                'purpose' => 'Consultation - Business Strategy', 'meeting_with' => 4, 'address_id' => 5,
                'mode' => 'Telephonic', 'created_by' => 4, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(10), 'updated_at' => now()->subHours(10)
            ],
            [
                'visitor_id' => 6, 'name_entered' => 'Meera Nair', 'mobile_number' => '+919876543215',
                'purpose' => 'Product Demo - New Software Features', 'meeting_with' => 4, 'address_id' => 6,
                'mode' => 'In-Campus', 'created_by' => 4, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(12), 'updated_at' => now()->subHours(12)
            ],
            [
                'visitor_id' => 5, 'name_entered' => 'Suresh Kumar', 'mobile_number' => '+919876543214',
                'purpose' => 'Follow-up Meeting - Implementation Plan', 'meeting_with' => 4, 'address_id' => 5,
                'mode' => 'In-Campus', 'created_by' => 4, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(9), 'updated_at' => now()->subHours(9)
            ],

            // Chennai Branch (Vikram - Has remark permissions)
            [
                'visitor_id' => 7, 'name_entered' => 'Deepak Sharma', 'mobile_number' => '+919876543216',
                'purpose' => 'Partnership Discussion - Joint Venture', 'meeting_with' => 5, 'address_id' => 7,
                'mode' => 'In-Campus', 'created_by' => 5, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(14), 'updated_at' => now()->subHours(14)
            ],
            [
                'visitor_id' => 8, 'name_entered' => 'Sunita Agarwal', 'mobile_number' => '+919876543217',
                'purpose' => 'Financial Review - Quarterly Reports', 'meeting_with' => 5, 'address_id' => 8,
                'mode' => 'In-Campus', 'created_by' => 5, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(16), 'updated_at' => now()->subHours(16)
            ],
            [
                'visitor_id' => 7, 'name_entered' => 'Deepak Sharma', 'mobile_number' => '+919876543216',
                'purpose' => 'Legal Documentation Review', 'meeting_with' => 5, 'address_id' => 7,
                'mode' => 'In-Campus', 'created_by' => 5, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(13), 'updated_at' => now()->subHours(13)
            ],

            // Kolkata Branch (Anita - No remark permissions)
            [
                'visitor_id' => 9, 'name_entered' => 'Rajesh Verma', 'mobile_number' => '+919876543218',
                'purpose' => 'HR Meeting - Employee Benefits', 'meeting_with' => 6, 'address_id' => 1,
                'mode' => 'Out-Campus', 'created_by' => 6, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(18), 'updated_at' => now()->subHours(18)
            ],
            [
                'visitor_id' => 10, 'name_entered' => 'Pooja Singh', 'mobile_number' => '+919876543219',
                'purpose' => 'Marketing Campaign - Brand Promotion', 'meeting_with' => 6, 'address_id' => 2,
                'mode' => 'In-Campus', 'created_by' => 6, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(20), 'updated_at' => now()->subHours(20)
            ],
            [
                'visitor_id' => 9, 'name_entered' => 'Rajesh Verma', 'mobile_number' => '+919876543218',
                'purpose' => 'Policy Update Discussion', 'meeting_with' => 6, 'address_id' => 1,
                'mode' => 'Telephonic', 'created_by' => 6, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(17), 'updated_at' => now()->subHours(17)
            ],

            // Cross-branch interactions to test permissions
            [
                'visitor_id' => 1, 'name_entered' => 'Arjun Mehta', 'mobile_number' => '+919876543210',
                'purpose' => 'Cross-branch Meeting - Delhi Office', 'meeting_with' => 3, 'address_id' => 3,
                'mode' => 'In-Campus', 'created_by' => 3, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(3), 'updated_at' => now()->subHours(3)
            ],
            [
                'visitor_id' => 3, 'name_entered' => 'Rohit Gupta', 'mobile_number' => '+919876543212',
                'purpose' => 'Cross-branch Meeting - Mumbai Office', 'meeting_with' => 2, 'address_id' => 1,
                'mode' => 'In-Campus', 'created_by' => 2, 'created_by_role' => 'staff',
                'created_at' => now()->subHours(7), 'updated_at' => now()->subHours(7)
            ],
        ];

        foreach ($interactions as $interactionData) {
            InteractionHistory::create($interactionData);
        }

        // 7. Create Remarks - More comprehensive remarks
        $remarks = [
            // Mumbai Branch (Priya) - Has remark permissions
            [
                'interaction_id' => 1, 'remark_text' => 'Candidate showed excellent technical skills. Good communication. Recommended for next round.',
                'added_by' => 2, 'added_by_name' => 'Priya Sharma',
                'created_at' => now()->subHours(1), 'updated_at' => now()->subHours(1)
            ],
            [
                'interaction_id' => 2, 'remark_text' => 'Client was satisfied with the project progress. Discussed timeline for next phase.',
                'added_by' => 2, 'added_by_name' => 'Priya Sharma',
                'created_at' => now()->subHours(3), 'updated_at' => now()->subHours(3)
            ],
            [
                'interaction_id' => 3, 'remark_text' => 'Technical round completed. Candidate performed well in coding test. HR round scheduled.',
                'added_by' => 2, 'added_by_name' => 'Priya Sharma',
                'created_at' => now()->subMinutes(30), 'updated_at' => now()->subMinutes(30)
            ],

            // Delhi Branch (Amit) - Has remark permissions
            [
                'interaction_id' => 4, 'remark_text' => 'Vendor agreed to reduce prices by 15%. Contract renewal discussion scheduled.',
                'added_by' => 3, 'added_by_name' => 'Amit Singh',
                'created_at' => now()->subHours(5), 'updated_at' => now()->subHours(5)
            ],
            [
                'interaction_id' => 5, 'remark_text' => 'Training completed successfully. New employee is ready to start work.',
                'added_by' => 3, 'added_by_name' => 'Amit Singh',
                'created_at' => now()->subHours(7), 'updated_at' => now()->subHours(7)
            ],
            [
                'interaction_id' => 6, 'remark_text' => 'Contract renewal terms finalized. New pricing structure approved.',
                'added_by' => 3, 'added_by_name' => 'Amit Singh',
                'created_at' => now()->subHours(4), 'updated_at' => now()->subHours(4)
            ],

            // Bangalore Branch (Sneha) - NO remark permissions (these remarks should not be visible to Sneha)
            [
                'interaction_id' => 7, 'remark_text' => 'Consultation went well. Client is interested in our services. Follow-up meeting scheduled.',
                'added_by' => 4, 'added_by_name' => 'Sneha Patel',
                'created_at' => now()->subHours(9), 'updated_at' => now()->subHours(9)
            ],
            [
                'interaction_id' => 8, 'remark_text' => 'Product demo was successful. Client wants to see pricing details.',
                'added_by' => 4, 'added_by_name' => 'Sneha Patel',
                'created_at' => now()->subHours(11), 'updated_at' => now()->subHours(11)
            ],
            [
                'interaction_id' => 9, 'remark_text' => 'Follow-up meeting scheduled for next week. Implementation plan to be discussed.',
                'added_by' => 4, 'added_by_name' => 'Sneha Patel',
                'created_at' => now()->subHours(8), 'updated_at' => now()->subHours(8)
            ],

            // Chennai Branch (Vikram) - Has remark permissions
            [
                'interaction_id' => 10, 'remark_text' => 'Partnership discussion is progressing well. Legal team to review terms.',
                'added_by' => 5, 'added_by_name' => 'Vikram Reddy',
                'created_at' => now()->subHours(13), 'updated_at' => now()->subHours(13)
            ],
            [
                'interaction_id' => 11, 'remark_text' => 'Financial review completed. All targets met. Next quarter planning in progress.',
                'added_by' => 5, 'added_by_name' => 'Vikram Reddy',
                'created_at' => now()->subHours(15), 'updated_at' => now()->subHours(15)
            ],
            [
                'interaction_id' => 12, 'remark_text' => 'Legal documentation reviewed. Minor changes required. Final draft ready by Friday.',
                'added_by' => 5, 'added_by_name' => 'Vikram Reddy',
                'created_at' => now()->subHours(12), 'updated_at' => now()->subHours(12)
            ],

            // Kolkata Branch (Anita) - NO remark permissions (these remarks should not be visible to Anita)
            [
                'interaction_id' => 13, 'remark_text' => 'HR meeting completed. Employee benefits package discussed and approved.',
                'added_by' => 6, 'added_by_name' => 'Anita Das',
                'created_at' => now()->subHours(17), 'updated_at' => now()->subHours(17)
            ],
            [
                'interaction_id' => 14, 'remark_text' => 'Marketing campaign approved. Budget allocated. Launch date confirmed.',
                'added_by' => 6, 'added_by_name' => 'Anita Das',
                'created_at' => now()->subHours(19), 'updated_at' => now()->subHours(19)
            ],
            [
                'interaction_id' => 15, 'remark_text' => 'Policy updates communicated to all employees. Implementation starts next month.',
                'added_by' => 6, 'added_by_name' => 'Anita Das',
                'created_at' => now()->subHours(16), 'updated_at' => now()->subHours(16)
            ],

            // Cross-branch interactions
            [
                'interaction_id' => 16, 'remark_text' => 'Cross-branch coordination meeting successful. Project timeline aligned.',
                'added_by' => 3, 'added_by_name' => 'Amit Singh',
                'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)
            ],
            [
                'interaction_id' => 17, 'remark_text' => 'Inter-branch collaboration meeting completed. Resource sharing plan finalized.',
                'added_by' => 2, 'added_by_name' => 'Priya Sharma',
                'created_at' => now()->subHours(6), 'updated_at' => now()->subHours(6)
            ],
        ];

        foreach ($remarks as $remarkData) {
            Remark::create($remarkData);
        }

        $this->command->info('Indian Demo Data Seeded Successfully!');
        $this->command->info('Admin Login: admin / admin123');
        $this->command->info('Staff Logins: priya/staff123, amit/staff123, sneha/staff123, vikram/staff123, anita/staff123');
    }
}
