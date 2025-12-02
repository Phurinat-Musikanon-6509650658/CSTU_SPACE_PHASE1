<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Admin (role_code = 32768)
        DB::table('user')->updateOrInsert(
            ['username_user' => 'admin'],
            [
                'firstname_user' => 'Admin',
                'lastname_user' => 'System',
                'user_code' => 'ADM',
                'role' => 32768,  // Admin role_code
                'email_user' => 'admin@cstu.ac.th',
                'password_user' => Hash::make('admin123'),
            ]
        );

        // Coordinator (role_code = 16384)
        DB::table('user')->updateOrInsert(
            ['username_user' => 'coordinator'],
            [
                'firstname_user' => 'ผู้ประสานงาน',
                'lastname_user' => 'ทดสอบ',
                'user_code' => 'CRD',
                'role' => 16384,  // Coordinator role_code
                'email_user' => 'coordinator@cstu.ac.th',
                'password_user' => Hash::make('coordinator123'),
            ]
        );

        // Lecturer/Advisor (role_code = 8192)
        DB::table('user')->updateOrInsert(
            ['username_user' => 'advisor'],
            [
                'firstname_user' => 'อาจารย์ที่ปรึกษา',
                'lastname_user' => 'ทดสอบ',
                'user_code' => 'ADV',
                'role' => 8192,  // Lecturer role_code
                'email_user' => 'advisor@cstu.ac.th',
                'password_user' => Hash::make('advisor123'),
            ]
        );

        // Staff (role_code = 4096)
        DB::table('user')->updateOrInsert(
            ['username_user' => 'staff'],
            [
                'firstname_user' => 'เจ้าหน้าที่',
                'lastname_user' => 'ทดสอบ',
                'user_code' => 'STF',
                'role' => 4096,  // Staff role_code
                'email_user' => 'staff@cstu.ac.th',
                'password_user' => Hash::make('staff123'),
            ]
        );

        /*
        // ข้อมูลเดิม
        DB::table('user')->updateOrInsert(
            ['username_user' => '6503640226'],
            [
                'firstname_user' => 'กันตินันท์',
                'lastname_user' => 'ตันติยาภินันท์',
                'user_code' => 'KTN',
                'role' => 32768,  // Admin role_code
                'email_user' => 'kantinan.tan@dome.tu.ac.th',
                'password_user' => Hash::make('1100703568130'),
            ]
        );

        DB::table('user')->updateOrInsert(
            ['username_user' => '6510470310'],
            [
                'firstname_user' => 'ภูรี',
                'lastname_user' => 'เข่งเจริญ',
                'user_code' => 'PHR',
                'role' => 16384,  // Coordinator role_code
                'email_user' => 'phuree.ken@dome.tu.ac.th',
                'password_user' => Hash::make('1102200195289'),
            ]
        );
        
        */

        // อาจารย์ที่ปรึกษาโครงงาน (19 ท่าน) - role_code = 8192 (Lecturer)
        $advisors = [
            [
                'username_user' => 'denduang',
                'firstname_user' => 'เด่นดวง',
                'lastname_user' => 'ประดับสุวรรณ',
                'user_code' => 'ddp',
                'role' => 8192,
                'email_user' => 'denduang@tu.ac.th',
                'password_user' => Hash::make('ddp2025')
            ],
            [
                'username_user' => 'wsaowalu',
                'firstname_user' => 'เสาวลักษณ์',
                'lastname_user' => 'วรรธนาภา',
                'user_code' => 'scw',
                'role' => 8192,
                'email_user' => 'wsaowalu@tu.ac.th',
                'password_user' => Hash::make('scw2025')
            ],
            [
                'username_user' => 'rongviri',
                'firstname_user' => 'ทรงศักดิ์',
                'lastname_user' => 'รองวิริยะพานิช',
                'user_code' => 'ssr',
                'role' => 8192,
                'email_user' => 'rongviri@tu.ac.th',
                'password_user' => Hash::make('ssr2025')
            ],
            [
                'username_user' => 'tanatorn',
                'firstname_user' => 'ธนาธร',
                'lastname_user' => 'ทะนานทอง',
                'user_code' => 'tnt',
                'role' => 8192,
                'email_user' => 'tanatorn@tu.ac.th',
                'password_user' => Hash::make('tnt2025')
            ],
            [
                'username_user' => 'pakornl',
                'firstname_user' => 'ปกรณ์',
                'lastname_user' => 'ลี้สุทธิพรชัย',
                'user_code' => 'pkl',
                'role' => 8192,
                'email_user' => 'pakornl@tu.ac.th',
                'password_user' => Hash::make('pkl2025')
            ],
            [
                'username_user' => 'rattanat',
                'firstname_user' => 'ประภาพร',
                'lastname_user' => 'รัตนธำรง',
                'user_code' => 'ppr',
                'role' => 8192,
                'email_user' => 'rattanat@tu.ac.th',
                'password_user' => Hash::make('ppr2025')
            ],
            [
                'username_user' => 'wirat',
                'firstname_user' => 'วิรัตน์',
                'lastname_user' => 'จารีวงศ์ไพบูลย์',
                'user_code' => 'wjr',
                'role' => 8192,
                'email_user' => 'wirat@tu.ac.th',
                'password_user' => Hash::make('wjr2025')
            ],
            [
                'username_user' => 'rwilawan',
                'firstname_user' => 'วิลาวรรณ',
                'lastname_user' => 'รักผกาวงศ์',
                'user_code' => 'wlr',
                'role' => 8192,
                'email_user' => 'rwilawan@tu.ac.th',
                'password_user' => Hash::make('wlr2025')
            ],
            [
                'username_user' => 'onjira',
                'firstname_user' => 'อรจิรา',
                'lastname_user' => 'สิทธิศักดิ์',
                'user_code' => 'ojs',
                'role' => 8192,
                'email_user' => 'onjira@tu.ac.th',
                'password_user' => Hash::make('ojs2025')
            ],
            [
                'username_user' => 'pakkp',
                'firstname_user' => 'ภัคพร',
                'lastname_user' => 'เสาร์ฝืน',
                'user_code' => 'pkp',
                'role' => 8192,
                'email_user' => 'pakkp@tu.ac.th',
                'password_user' => Hash::make('pkp2025')
            ],
            [
                'username_user' => 'ckasidit',
                'firstname_user' => 'กษิดิศ',
                'lastname_user' => 'ชาญเชี่ยว',
                'user_code' => 'kdc',
                'role' => 8192,
                'email_user' => 'ckasidit@tu.ac.th',
                'password_user' => Hash::make('kdc2025')
            ],
            [
                'username_user' => 'thapanab',
                'firstname_user' => 'ฐาปนา',
                'lastname_user' => 'บุญชู',
                'user_code' => 'tpb',
                'role' => 8192,
                'email_user' => 'thapanab@tu.ac.th',
                'password_user' => Hash::make('tpb2025')
            ],
            [
                'username_user' => 'pokpongs',
                'firstname_user' => 'ปกป้อง',
                'lastname_user' => 'ส่องเมือง',
                'user_code' => 'pps',
                'role' => 8192,
                'email_user' => 'pokpongs@tu.ac.th',
                'password_user' => Hash::make('pps2025')
            ],
            [
                'username_user' => 'lumpapun',
                'firstname_user' => 'ลัมพาพรรณ',
                'lastname_user' => 'พันธุ์ซูจิตร์',
                'user_code' => 'lpp',
                'role' => 8192,
                'email_user' => 'lumpapun@tu.ac.th',
                'password_user' => Hash::make('lpp2025')
            ],
            [
                'username_user' => 'pwanida',
                'firstname_user' => 'วนิดา',
                'lastname_user' => 'พฤทธิวิทยา',
                'user_code' => 'wdp',
                'role' => 8192,
                'email_user' => 'pwanida@tu.ac.th',
                'password_user' => Hash::make('wdp2025')
            ],
            [
                'username_user' => 'nnuchako',
                'firstname_user' => 'นุชซากร',
                'lastname_user' => 'งามเสาวรส',
                'user_code' => 'nng',
                'role' => 8192,
                'email_user' => 'nnuchako@tu.ac.th',
                'password_user' => Hash::make('nng2025')
            ],
            [
                'username_user' => 'nsirikun',
                'firstname_user' => 'สิริกันยา',
                'lastname_user' => 'นิลพานิช',
                'user_code' => 'skn',
                'role' => 8192,
                'email_user' => 'nsirikun@tu.ac.th',
                'password_user' => Hash::make('skn2025')
            ],
            [
                'username_user' => 'satanat',
                'firstname_user' => 'ศาตนาฏ',
                'lastname_user' => 'กิจศิรานุวัตร',
                'user_code' => 'snk',
                'role' => 8192,
                'email_user' => 'satanat@tu.ac.th',
                'password_user' => Hash::make('snk2025')
            ],
            [
                'username_user' => 'nawarerk',
                'firstname_user' => 'นวฤกษ์',
                'lastname_user' => 'ชลารักษ์',
                'user_code' => 'nrc',
                'role' => 8192,
                'email_user' => 'nawarerk@tu.ac.th',
                'password_user' => Hash::make('nrc2025')
            ]
        ];

        foreach ($advisors as $advisor) {
            DB::table('user')->updateOrInsert(
                ['username_user' => $advisor['username_user']],
                [
                    'firstname_user' => $advisor['firstname_user'],
                    'lastname_user' => $advisor['lastname_user'],
                    'user_code' => $advisor['user_code'],
                    'role' => $advisor['role'],
                    'email_user' => $advisor['email_user'],
                    'password_user' => $advisor['password_user'],
                ]
            );
        }
    }
}
