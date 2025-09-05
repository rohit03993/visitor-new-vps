<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run()
    {
        try {
            $this->command->info('Starting to seed demo data...');
            
            // Disable foreign key checks to avoid circular dependency issues
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            
            // Clear existing data (except admin user and branches)
            $this->command->info('Clearing existing demo data...');
            DB::table('remarks')->delete();
            DB::table('interaction_history')->delete();
            DB::table('visitors')->delete();
            DB::table('addresses')->delete();
            
            // Clear demo users (keep admin user with ID 1)
            DB::table('vms_users')->where('user_id', '>', 1)->delete();
            
            $this->command->info('Demo data cleared successfully.');
            
            // Create demo addresses
            $this->command->info('Creating demo addresses...');
            $addresses = [
                [
                    'address_id' => 1,
                    'address_name' => 'Rajpur Chungi, Delhi',
                    'full_address' => 'Rajpur Chungi, New Delhi, Delhi 110001, India',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'address_id' => 2,
                    'address_name' => 'Rohta, Meerut',
                    'full_address' => 'Rohta, Meerut, Uttar Pradesh 250001, India',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'address_id' => 3,
                    'address_name' => 'Dev Nagar, Ghaziabad',
                    'full_address' => 'Dev Nagar, Ghaziabad, Uttar Pradesh 201001, India',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'address_id' => 4,
                    'address_name' => 'Connaught Place, New Delhi',
                    'full_address' => 'Connaught Place, New Delhi, Delhi 110001, India',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'address_id' => 5,
                    'address_name' => 'Sector 62, Noida',
                    'full_address' => 'Sector 62, Noida, Uttar Pradesh 201301, India',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'address_id' => 6,
                    'address_name' => 'Cyber City, Gurgaon',
                    'full_address' => 'Cyber City, Gurgaon, Haryana 122002, India',
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            DB::table('addresses')->insert($addresses);
            $this->command->info('Demo addresses created: ' . count($addresses));
            
            // Create demo front desk users (2 per branch)
            $this->command->info('Creating demo front desk users...');
            $frontDeskUsers = [
                [
                    'user_id' => 15,
                    'username' => 'fd_priya_sharma',
                    'password' => Hash::make('frontdesk123'),
                    'name' => 'Priya Sharma',
                    'role' => 'frontdesk',
                    'branch_id' => 1,
                    'mobile_number' => '+919876543211',
                    'can_view_remarks' => 1,
                    'can_download_excel' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'user_id' => 16,
                    'username' => 'fd_amit_patel',
                    'password' => Hash::make('frontdesk123'),
                    'name' => 'Amit Patel',
                    'role' => 'frontdesk',
                    'branch_id' => 1,
                    'mobile_number' => '+919876543212',
                    'can_view_remarks' => 0,
                    'can_download_excel' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'user_id' => 17,
                    'username' => 'fd_neha_singh',
                    'password' => Hash::make('frontdesk123'),
                    'name' => 'Neha Singh',
                    'role' => 'frontdesk',
                    'branch_id' => 2,
                    'mobile_number' => '+919876543213',
                    'can_view_remarks' => 1,
                    'can_download_excel' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'user_id' => 18,
                    'username' => 'fd_rahul_verma',
                    'password' => Hash::make('frontdesk123'),
                    'name' => 'Rahul Verma',
                    'role' => 'frontdesk',
                    'branch_id' => 2,
                    'mobile_number' => '+919876543214',
                    'can_view_remarks' => 1,
                    'can_download_excel' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'user_id' => 19,
                    'username' => 'fd_anjali_gupta',
                    'password' => Hash::make('frontdesk123'),
                    'name' => 'Anjali Gupta',
                    'role' => 'frontdesk',
                    'branch_id' => 3,
                    'mobile_number' => '+919876543215',
                    'can_view_remarks' => 0,
                    'can_download_excel' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'user_id' => 20,
                    'username' => 'fd_vikram_malhotra',
                    'password' => Hash::make('frontdesk123'),
                    'name' => 'Vikram Malhotra',
                    'role' => 'frontdesk',
                    'branch_id' => 3,
                    'mobile_number' => '+919876543216',
                    'can_view_remarks' => 1,
                    'can_download_excel' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            DB::table('vms_users')->insert($frontDeskUsers);
            $this->command->info('Demo front desk users created: ' . count($frontDeskUsers));
            
            // Create demo employee users (2 per branch)
            $this->command->info('Creating demo employee users...');
            $employeeUsers = [
                [
                    'user_id' => 21,
                    'username' => 'emp_suresh_kumar',
                    'password' => Hash::make('employee123'),
                    'name' => 'Suresh Kumar',
                    'role' => 'employee',
                    'branch_id' => 1,
                    'mobile_number' => '+919876543217',
                    'can_view_remarks' => 1,
                    'can_download_excel' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'user_id' => 22,
                    'username' => 'emp_meera_devi',
                    'password' => Hash::make('employee123'),
                    'name' => 'Meera Devi',
                    'role' => 'employee',
                    'branch_id' => 1,
                    'mobile_number' => '+919876543218',
                    'can_view_remarks' => 0,
                    'can_download_excel' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'user_id' => 23,
                    'username' => 'emp_arjun_singh',
                    'password' => Hash::make('employee123'),
                    'name' => 'Arjun Singh',
                    'role' => 'employee',
                    'branch_id' => 2,
                    'mobile_number' => '+919876543219',
                    'can_view_remarks' => 1,
                    'can_download_excel' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'user_id' => 24,
                    'username' => 'emp_kavita_sharma',
                    'password' => Hash::make('employee123'),
                    'name' => 'Kavita Sharma',
                    'role' => 'employee',
                    'branch_id' => 2,
                    'mobile_number' => '+919876543220',
                    'can_view_remarks' => 0,
                    'can_download_excel' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'user_id' => 25,
                    'username' => 'emp_ramesh_patel',
                    'password' => Hash::make('employee123'),
                    'name' => 'Ramesh Patel',
                    'role' => 'employee',
                    'branch_id' => 3,
                    'mobile_number' => '+919876543221',
                    'can_view_remarks' => 1,
                    'can_download_excel' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'user_id' => 26,
                    'username' => 'emp_sunita_verma',
                    'password' => Hash::make('employee123'),
                    'name' => 'Sunita Verma',
                    'role' => 'employee',
                    'branch_id' => 3,
                    'mobile_number' => '+919876543222',
                    'can_view_remarks' => 0,
                    'can_download_excel' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            DB::table('vms_users')->insert($employeeUsers);
            $this->command->info('Demo employee users created: ' . count($employeeUsers));
            
            // Create demo visitors
            $this->command->info('Creating demo visitors...');
            $visitors = [
                [
                    'visitor_id' => 1,
                    'name' => 'Rajesh Malhotra',
                    'mobile_number' => '+919876543230',
                    'purpose' => 'Business Meeting',
                    'address_id' => 1,
                    'created_by' => 15,
                    'last_updated_by' => 15,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'visitor_id' => 2,
                    'name' => 'Priya Singh',
                    'mobile_number' => '+919876543231',
                    'purpose' => 'Interview',
                    'address_id' => 2,
                    'created_by' => 17,
                    'last_updated_by' => 17,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'visitor_id' => 3,
                    'name' => 'Amit Kumar',
                    'mobile_number' => '+919876543232',
                    'purpose' => 'Client Meeting',
                    'address_id' => 3,
                    'created_by' => 19,
                    'last_updated_by' => 19,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'visitor_id' => 4,
                    'name' => 'Neha Sharma',
                    'mobile_number' => '+919876543233',
                    'purpose' => 'New Admission',
                    'address_id' => 4,
                    'created_by' => 15,
                    'last_updated_by' => 15,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'visitor_id' => 5,
                    'name' => 'Rahul Verma',
                    'mobile_number' => '+919876543234',
                    'purpose' => 'Marketing',
                    'address_id' => 5,
                    'created_by' => 17,
                    'last_updated_by' => 17,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'visitor_id' => 6,
                    'name' => 'Anjali Gupta',
                    'mobile_number' => '+919876543235',
                    'purpose' => 'News & Media',
                    'address_id' => 6,
                    'created_by' => 19,
                    'last_updated_by' => 19,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            DB::table('visitors')->insert($visitors);
            $this->command->info('Demo visitors created: ' . count($visitors));
            
            // Create demo interactions
            $this->command->info('Creating demo interactions...');
            $interactions = [
                [
                    'interaction_id' => 1,
                    'visitor_id' => 1,
                    'name_entered' => 'Rajesh Malhotra',
                    'mode' => 'In-Campus',
                    'purpose' => 'Parent',
                    'address_id' => 1,
                    'meeting_with' => 21,
                    'created_by' => 15,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'interaction_id' => 2,
                    'visitor_id' => 2,
                    'name_entered' => 'Priya Singh',
                    'mode' => 'Out-Campus',
                    'purpose' => 'Student',
                    'address_id' => 2,
                    'meeting_with' => 23,
                    'created_by' => 17,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'interaction_id' => 3,
                    'visitor_id' => 3,
                    'name_entered' => 'Amit Kumar',
                    'mode' => 'Telephonic',
                    'purpose' => 'Ex-student',
                    'address_id' => 3,
                    'meeting_with' => 25,
                    'created_by' => 19,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'interaction_id' => 4,
                    'visitor_id' => 4,
                    'name_entered' => 'Neha Sharma',
                    'mode' => 'In-Campus',
                    'purpose' => 'New Admission',
                    'address_id' => 4,
                    'meeting_with' => 21,
                    'created_by' => 15,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'interaction_id' => 5,
                    'visitor_id' => 5,
                    'name_entered' => 'Rahul Verma',
                    'mode' => 'Out-Campus',
                    'purpose' => 'Marketing',
                    'address_id' => 5,
                    'meeting_with' => 23,
                    'created_by' => 17,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'interaction_id' => 6,
                    'visitor_id' => 6,
                    'name_entered' => 'Anjali Gupta',
                    'mode' => 'Telephonic',
                    'purpose' => 'News & Media',
                    'address_id' => 6,
                    'meeting_with' => 25,
                    'created_by' => 19,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            DB::table('interaction_history')->insert($interactions);
            $this->command->info('Demo interactions created: ' . count($interactions));
            
            // Create demo remarks
            $this->command->info('Creating demo remarks...');
            $remarks = [
                [
                    'remark_id' => 1,
                    'interaction_id' => 1,
                    'remark_text' => 'Visitor arrived on time. Business meeting completed successfully. Discussed partnership opportunities.',
                    'added_by' => 21,
                    'is_editable_by' => 21,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'remark_id' => 2,
                    'interaction_id' => 2,
                    'remark_text' => 'Out-campus interview scheduled for next week. Candidate showed good potential.',
                    'added_by' => 23,
                    'is_editable_by' => 23,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'remark_id' => 3,
                    'interaction_id' => 3,
                    'remark_text' => 'Phone call completed. Client interested in our services. Follow-up required.',
                    'added_by' => 25,
                    'is_editable_by' => 25,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'remark_id' => 4,
                    'interaction_id' => 4,
                    'remark_text' => 'New admission inquiry. Student interested in Computer Science program.',
                    'added_by' => 21,
                    'is_editable_by' => 21,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'remark_id' => 5,
                    'interaction_id' => 5,
                    'remark_text' => 'Marketing discussion held. Company interested in advertising partnership.',
                    'added_by' => 23,
                    'is_editable_by' => 23,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'remark_id' => 6,
                    'interaction_id' => 6,
                    'remark_text' => 'Media inquiry about upcoming events. Provided press kit and contact information.',
                    'added_by' => 25,
                    'is_editable_by' => 25,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ];
            
            DB::table('remarks')->insert($remarks);
            $this->command->info('Demo remarks created: ' . count($remarks));
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->command->info('✅ Demo data seeded successfully!');
            $this->command->info('📋 Admin login: admin / admin123');
            $this->command->info('📋 Front Desk login: fd_priya_sharma / frontdesk123');
            $this->command->info('📋 Employee login: emp_suresh_kumar / employee123');
            $this->command->info('📊 Created: 6 addresses, 12 users, 6 visitors, 6 interactions, 6 remarks');
            
        } catch (\Exception $e) {
            $this->command->error('❌ Error seeding demo data: ' . $e->getMessage());
            $this->command->error('Stack trace: ' . $e->getTraceAsString());
            
            // Re-enable foreign key checks even if there's an error
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            throw $e;
        }
    }
}
