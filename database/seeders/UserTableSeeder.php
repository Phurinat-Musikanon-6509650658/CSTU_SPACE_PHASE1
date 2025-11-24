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

        // อาจารย์ที่ปรึกษาโครงงาน (19 ท่าน)
        $advisors = [
            [
                'username_user' => 'denduang.p',
                'firstname_user' => 'เด่นดวง',
                'lastname_user' => 'ปราบศัตรู',
                'user_code' => 'ddp',
                'role' => 'advisor',
                'email_user' => 'denduang@tu.ac.th',
                'password_user' => Hash::make('ddp2025')
            ],
            [
                'username_user' => 'wsaowalu.w',
                'firstname_user' => 'เสาวลักษณ์',
                'lastname_user' => 'วรรธนาภา',
                'user_code' => 'scw',
                'role' => 'advisor',
                'email_user' => 'wsaowalu@tu.ac.th',
                'password_user' => Hash::make('scw2025')
            ],
            [
                'username_user' => 'rongsak.r',
                'firstname_user' => 'ทรงศักดิ์',
                'lastname_user' => 'รองวิริยะพานิช',
                'user_code' => 'ssr',
                'role' => 'advisor',
                'email_user' => 'rongviri@tu.ac.th',
                'password_user' => Hash::make('ssr2025')
            ],
            [
                'username_user' => 'tanatorn.t',
                'firstname_user' => 'ธนาธร',
                'lastname_user' => 'ทะนานทอง',
                'user_code' => 'tnt',
                'role' => 'advisor',
                'email_user' => 'tanatorn@tu.ac.th',
                'password_user' => Hash::make('tnt2025')
            ],
            [
                'username_user' => 'pakorn.l',
                'firstname_user' => 'ปกรณ์',
                'lastname_user' => 'ลีสุทธิพรชัย',
                'user_code' => 'pkl',
                'role' => 'advisor',
                'email_user' => 'pakornl@tu.ac.th',
                'password_user' => Hash::make('pkl2025')
            ],
            [
                'username_user' => 'praphaporn.r',
                'firstname_user' => 'ประภาพร',
                'lastname_user' => 'รัศนธารานิ',
                'user_code' => 'ppr',
                'role' => 'advisor',
                'email_user' => 'rattanat@tu.ac.th',
                'password_user' => Hash::make('ppr2025')
            ],
            [
                'username_user' => 'wirat.j',
                'firstname_user' => 'วิรัตน์',
                'lastname_user' => 'จารึงศุภโฆษ',
                'user_code' => 'wjr',
                'role' => 'advisor',
                'email_user' => 'wirat@tu.ac.th',
                'password_user' => Hash::make('wjr2025')
            ],
            [
                'username_user' => 'wilawan.r',
                'firstname_user' => 'วีลาวรรณ',
                'lastname_user' => 'รักษ์การงานดี',
                'user_code' => 'wlr',
                'role' => 'advisor',
                'email_user' => 'rwilawan@tu.ac.th',
                'password_user' => Hash::make('wlr2025')
            ],
            [
                'username_user' => 'ornjira.s',
                'firstname_user' => 'อรจิรา',
                'lastname_user' => 'สิทธิ์ศักดิ์',
                'user_code' => 'ojs',
                'role' => 'advisor',
                'email_user' => 'onjira@tu.ac.th',
                'password_user' => Hash::make('ojs2025')
            ],
            [
                'username_user' => 'phakpor.s',
                'firstname_user' => 'ภักพร',
                'lastname_user' => 'เสาร์ฝั่น',
                'user_code' => 'pkp',
                'role' => 'advisor',
                'email_user' => 'pakkp@tu.ac.th',
                'password_user' => Hash::make('pkp2025')
            ],
            [
                'username_user' => 'kasidit.c',
                'firstname_user' => 'กษิดิ์',
                'lastname_user' => 'ชาญเชี่ยว',
                'user_code' => 'kdc',
                'role' => 'advisor',
                'email_user' => 'ckasidit@tu.ac.th',
                'password_user' => Hash::make('kdc2025')
            ],
            [
                'username_user' => 'thapanee.b',
                'firstname_user' => 'ธาปนีย์',
                'lastname_user' => 'บุบผา',
                'user_code' => 'tpb',
                'role' => 'advisor',
                'email_user' => 'thapanab@tu.ac.th',
                'password_user' => Hash::make('tpb2025')
            ],
            [
                'username_user' => 'pokpong.s',
                'firstname_user' => 'ปกป้อง',
                'lastname_user' => 'สองเมือง',
                'user_code' => 'pps',
                'role' => 'advisor',
                'email_user' => 'pokpongs@tu.ac.th',
                'password_user' => Hash::make('pps2025')
            ],
            [
                'username_user' => 'lumpapan.p',
                'firstname_user' => 'ลัมพาพรรณ',
                'lastname_user' => 'พันธุ์รุจิกร',
                'user_code' => 'lpp',
                'role' => 'advisor',
                'email_user' => 'lumpapun@tu.ac.th',
                'password_user' => Hash::make('lpp2025')
            ],
            [
                'username_user' => 'wanida.p',
                'firstname_user' => 'วนิดา',
                'lastname_user' => 'พฤฒาธิวัฒนา',
                'user_code' => 'wdp',
                'role' => 'advisor',
                'email_user' => 'pwanida@tu.ac.th',
                'password_user' => Hash::make('wdp2025')
            ],
            [
                'username_user' => 'nuchjakorn.n',
                'firstname_user' => 'นุชจากร',
                'lastname_user' => 'งามเสาวรส',
                'user_code' => 'nng',
                'role' => 'advisor',
                'email_user' => 'nnuchako@tu.ac.th',
                'password_user' => Hash::make('nng2025')
            ],
            [
                'username_user' => 'sirikanya.n',
                'firstname_user' => 'สิริกัญญา',
                'lastname_user' => 'นิลพานิช',
                'user_code' => 'skn',
                'role' => 'advisor',
                'email_user' => 'nsirikun@tu.ac.th',
                'password_user' => Hash::make('skn2025')
            ],
            [
                'username_user' => 'sanan.k',
                'firstname_user' => 'ศานนท์',
                'lastname_user' => 'กิจศิราบุศดอร์',
                'user_code' => 'snk',
                'role' => 'advisor',
                'email_user' => 'satanat@tu.ac.th',
                'password_user' => Hash::make('snk2025')
            ],
            [
                'username_user' => 'nawaresh.c',
                'firstname_user' => 'นวเรศ',
                'lastname_user' => 'ชลาเรศ',
                'user_code' => 'nrc',
                'role' => 'advisor',
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
