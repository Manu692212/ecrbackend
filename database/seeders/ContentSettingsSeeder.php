<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class ContentSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'home.hero',
                'group' => 'home',
                'description' => 'Home hero section content',
                'data' => [
                    'mainHeading' => "Welcome to India's Largest Aviation College",
                    'subHeading' => 'ECR Academy for Professional Training and Placements - A premier institute dedicated to shaping the future of Aviation & Logistics.',
                    'badgeText' => 'University Approved Institution',
                    'enrollButtonText' => 'Enroll Now',
                    'contactButtonText' => 'Contact Us',
                    'trustBadgeText' => 'Trusted by 1,00,000+ Students',
                ],
            ],
            [
                'key' => 'home.stats',
                'group' => 'home',
                'description' => 'Home statistics section',
                'data' => [
                    ['icon' => 'Users', 'value' => 150, 'suffix' => '+', 'label' => 'Teachers'],
                    ['icon' => 'BookOpen', 'value' => 20, 'suffix' => '+', 'label' => 'Courses'],
                    ['icon' => 'GraduationCap', 'value' => 7000, 'suffix' => '+', 'label' => 'Students'],
                    ['icon' => 'Building2', 'value' => 40, 'suffix' => ' Acres', 'label' => 'Campus'],
                ],
            ],
            [
                'key' => 'home.courses_overview',
                'group' => 'home',
                'description' => 'Homepage featured courses/categories',
                'data' => [
                    [
                        'icon' => 'Briefcase',
                        'title' => 'Management AHM',
                        'description' => 'Aviation and Hospitality Management combined with traditional degree programs.',
                        'courses' => [
                            ['name' => 'BCA + Aviation and Hospitality Management (AHM)', 'duration' => '3 Years', 'eligibility' => '10+2'],
                            ['name' => 'BBA + Aviation and Hospitality Management (AHM)', 'duration' => '3 Years', 'eligibility' => '10+2'],
                            ['name' => 'B.Com + Aviation and Hospitality Management (AHM)', 'duration' => '3 Years', 'eligibility' => '10+2'],
                        ],
                    ],
                    [
                        'icon' => 'BookOpen',
                        'title' => 'Management ADD ONS',
                        'description' => 'Enhance your degree with cutting-edge technology and business skills.',
                        'courses' => [
                            ['name' => 'BBA/BCA/B.Com + Artificial Intelligence', 'duration' => '3 Years', 'eligibility' => '10+2'],
                            ['name' => 'BBA/BCA/B.Com + Cyber Security', 'duration' => '3 Years', 'eligibility' => '10+2'],
                            ['name' => 'BBA/BCA/B.Com + Big Data Analytics', 'duration' => '3 Years', 'eligibility' => '10+2'],
                            ['name' => 'BBA/BCA/B.Com + Digital Marketing', 'duration' => '3 Years', 'eligibility' => '10+2'],
                            ['name' => 'BBA/BCA/B.Com + Supply Chain Logistics Management', 'duration' => '3 Years', 'eligibility' => '10+2'],
                        ],
                    ],
                    [
                        'icon' => 'Heart',
                        'title' => 'Paramedical',
                        'description' => 'Healthcare support courses for aspiring medical professionals.',
                        'courses' => [
                            ['name' => 'Paramedical Course', 'duration' => '2 Years', 'eligibility' => '10+2 with Science'],
                        ],
                    ],
                    [
                        'icon' => 'Stethoscope',
                        'title' => 'Nursing',
                        'description' => 'Professional nursing programs recognized by Indian Nursing Council.',
                        'courses' => [
                            ['name' => 'BSC Nursing', 'duration' => '4 Years', 'eligibility' => '10+2 with Science'],
                            ['name' => 'GNM Nursing', 'duration' => '3.5 Years', 'eligibility' => '10+2'],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'about.content',
                'group' => 'about',
                'description' => 'About page content blocks',
                'data' => [
                    'title' => 'ECR Group of Institutions',
                    'subtitle' => 'Reformation through education and charity - shaping the future of Aviation & Logistics',
                    'description' => [
                        'ECR Group of Institutions, under the ECR Trust, stands out with its innovative approach to education. Spread across approximately 40 acres, the campus is equipped with state-of-the-art facilities and offers a variety of degree courses approved by AICTE, Indian Nursing Council, Mangalore University, and Rajiv Gandhi University of Health Sciences.',
                        'Unlike conventional degree courses such as BBA, BCA, B.Com, Aviation, Hotel Management, and Fashion Designing, which primarily focus on academics and achieving high results, ECR goes a step further. The institution emphasizes job-oriented courses like Artificial Intelligence, Data Science, Digital Marketing, Cyber Security, Airport Management, HR, Marketing, Finance, and Operations in collaboration with foreign companies.',
                        'Part-time job opportunities in areas like HR, Finance, Operations, Advertising, Share Trading Consultancy, and Event Management are provided within the campus. Students earn a monthly income through these jobs, transitioning from candidates to professionals.',
                        'ECR\'s unique vision was inspired by the realization that even students who graduate with high marks often struggle to secure well-paying jobs. The issue lies not in their abilities but in the traditional education methods, which fail to prepare them for the demands of the real world. ECR bridges this gap by focusing on planning and development to cultivate essential skills.',
                    ],
                    'achievements' => [
                        'AICTE Approved Programs',
                        'Indian Nursing Council Recognition',
                        'Mangalore University Affiliation',
                        'Rajiv Gandhi University of Health Sciences Partnership',
                        '40+ Acres State-of-the-art Campus',
                        'International Collaboration with Foreign Companies',
                    ],
                    'stats' => [
                        'distinctions' => '90%',
                        'placement' => '100%',
                        'ranks' => '4-5',
                        'students' => '1L+',
                    ],
                    'values' => [
                        [
                            'title' => 'Vision',
                            'description' => 'To be the leading educational institution that transforms students into industry-ready professionals with global competence.',
                        ],
                        [
                            'title' => 'Mission',
                            'description' => 'To provide quality education combined with practical training, ensuring every student is equipped for success.',
                        ],
                        [
                            'title' => 'Excellence',
                            'description' => 'Consistently achieving 4 to 5 top ranks at the university level with 90% of students earning distinctions.',
                        ],
                    ],
                ],
            ],
            [
                'key' => 'contact.info',
                'group' => 'contact',
                'description' => 'Contact page information',
                'data' => [
                    'title' => 'Get in Touch',
                    'subtitle' => "We're here to help you with any questions about our programs and admissions",
                    'address' => 'ECR Group of Institutions, Airport Road, Mangalore, Karnataka - 575030',
                    'phone' => '+91 1234567890',
                    'altPhone' => '+91 9876543210',
                    'email' => 'info@ecracademy.com',
                    'workingHours' => 'Monday - Friday: 9:00 AM - 6:00 PM, Saturday: 9:00 AM - 1:00 PM',
                    'mapEmbed' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3888.5!2d74.8!3d12.9!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0!2zMTLCsDA1JzQwLjAiTiA3NMKwNDgnMDAuMCJF!5e0!3m2!1sen!2sin!4v1234567890" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>',
                    'socialMedia' => [
                        'facebook' => 'https://facebook.com/ecracademy',
                        'twitter' => 'https://twitter.com/ecracademy',
                        'linkedin' => 'https://linkedin.com/company/ecracademy',
                        'instagram' => 'https://instagram.com/ecracademy',
                        'youtube' => 'https://youtube.com/@ecracademy',
                    ],
                ],
            ],
            [
                'key' => 'admission.content',
                'group' => 'admission',
                'description' => 'Admission page content',
                'data' => [
                    'title' => 'Admission Process',
                    'subtitle' => 'Join ECR Academy - Your Gateway to Aviation Excellence',
                    'description' => 'Our admission process is designed to be simple and transparent. Follow the steps below to begin your journey with us.',
                    'process' => [
                        ['step' => 1, 'title' => 'Application Form', 'description' => 'Fill out the online application form with your personal and academic details.'],
                        ['step' => 2, 'title' => 'Document Submission', 'description' => 'Submit required documents including mark sheets, ID proof, and photographs.'],
                        ['step' => 3, 'title' => 'Counseling Session', 'description' => 'Attend a counseling session to understand the program and career opportunities.'],
                        ['step' => 4, 'title' => 'Fee Payment', 'description' => 'Complete the admission process by paying the required fees.'],
                    ],
                    'requirements' => [
                        [
                            'category' => 'Academic Requirements',
                            'items' => [
                                '10+2 or equivalent from recognized board',
                                'Minimum 50% marks in qualifying examination',
                                'Physics and Mathematics compulsory for Aviation courses',
                            ],
                        ],
                        [
                            'category' => 'Documents Required',
                            'items' => [
                                '10th and 12th mark sheets',
                                'Transfer Certificate',
                                'Migration Certificate (if applicable)',
                                'Passport size photographs (4)',
                                'Aadhar Card or ID proof',
                            ],
                        ],
                    ],
                    'importantDates' => [
                        ['event' => 'Admission Start', 'date' => '2024-01-15', 'description' => 'Online applications open'],
                        ['event' => 'Last Date for Application', 'date' => '2024-05-31', 'description' => 'Submission deadline'],
                        ['event' => 'Counseling Begins', 'date' => '2024-06-01', 'description' => 'Counseling sessions start'],
                    ],
                    'contactInfo' => [
                        'phone' => '+91 1234567890',
                        'email' => 'admissions@ecracademy.com',
                        'office' => 'Admission Office, ECR Campus, Mangalore',
                    ],
                ],
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'group' => $setting['group'],
                    'type' => 'json',
                    'description' => $setting['description'] ?? null,
                    'value' => json_encode($setting['data']),
                    'is_public' => true,
                ]
            );
        }
    }
}
