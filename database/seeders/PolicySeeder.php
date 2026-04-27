<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Policy;

class PolicySeeder extends Seeder
{
    public function run(): void
    {
        $policies = [
            [
                'title' => 'Senate Accountability and Core Functions Policy',
                'category' => 'Governance',
                'content' => 'The Senate is accountable to the President and is the supreme body for core academic matters, including approving academic calendars and programs, conferring degrees, setting admission and academic standards, overseeing quality assurance, and formulating policies for staff promotion, disciplinary guidelines, and resource utilization.',
            ],
            [
                'title' => 'Academic Freedom Policy',
                'category' => 'Academic',
                'content' => 'Academic staff have the right to exercise academic freedom, including the freedom to teach, research, write, learn, and disseminate information without interference, subject to universal principles of scientific inquiry and public health and morality exceptions. This also includes freedom of association and publication.',
            ],
            [
                'title' => 'Study Leave Policy',
                'category' => 'Academic',
                'content' => 'Academic staff may be granted study leave for higher degrees after a minimum of two years of service, provided it aligns with the department\'s staff development scheme. Staff must be under 45, submit biannual progress reports, and sign an undertaking to return and serve; failure to return results in liability for damages.',
            ],
            [
                'title' => 'Sabbatical Leave Policy',
                'category' => 'Academic',
                'content' => 'A full-time academic staff member is entitled to a one-year sabbatical leave with full pay after seven years of continuous service, provided they present a scholarly program and sign an undertaking to return for at least one year. Failure to return entitles the University to claim paid salaries plus damages.',
            ],
            [
                'title' => 'Academic Tenure Policy',
                'category' => 'Academic',
                'content' => 'Tenure, a privilege and incentive for professional excellence, may be awarded to full-time Associate Professors (or above) with ten years of service, or Assistant Professors with ten years in that status. A tenured staff member has job security and can only be dismissed for a serious breach of discipline.',
            ],
            [
                'title' => 'Academic Staff Promotion Criteria Policy',
                'category' => 'Academic',
                'content' => 'Promotion of academic staff is based on criteria including length of service in rank, effectiveness in teaching (measured by student and peer evaluations), research publications (books, journals, patents), participation in university affairs, and community/consultancy services.',
            ],
            [
                'title' => 'Emeritus Professor Designation Policy',
                'category' => 'Academic',
                'content' => 'The honorific title of "Emeritus" may be awarded upon retirement to distinguished scholars holding the rank of Associate Professor or Professor who retire directly from the university. The designation provides privileges like library access, campus event invitations, and listing in university publications, with no formal duties or remuneration.',
            ],
            [
                'title' => 'Senate Standing Committee Mandate Policy',
                'category' => 'Governance',
                'content' => 'The Senate operates through five main Standing Committees (ASQAC, SARC, RTTC, ASAC, ECCC) which function as its arms. They monitor legislation implementation, propose new policies, make interim decisions between Senate meetings, and report biannually to the Senate.',
            ],
            [
                'title' => 'Student Admission and Placement Policy',
                'category' => 'Academic',
                'content' => 'Admission to regular undergraduate programs is based on the Ethiopian Higher Education Entrance Examination (EHEEE) and ASTU-specific criteria. 20% of all places are reserved for female students, in addition to their right to compete for the remaining 80%. Special admissions for disadvantaged groups may not exceed 5% of a program\'s intake.',
            ],
            [
                'title' => 'Illegal Admission and Forgery Policy',
                'category' => 'Academic',
                'content' => 'Securing admission by any means other than the official university system, including using forged documents, is illegal. Discovery at any time leads to immediate and permanent dismissal from the university, and the case may be referred to the courts.',
            ],
            // ... (add all other policies in the same format)
        ];

        foreach ($policies as $policy) {
            Policy::create([
                'title' => $policy['title'],
                'category' => $policy['category'],
                'content' => $policy['content'],
                'is_active' => true,
            ]);
        }
    }
}
