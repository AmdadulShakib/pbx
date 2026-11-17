<?php

namespace Database\Seeders;

use App\Models\PbxCallLog;
use App\Models\PbxExtension;
use App\Models\PbxInboundRoute;
use App\Models\PbxIvr;
use App\Models\PbxIvrOption;
use App\Models\PbxOutboundRoute;
use App\Models\PbxRingGroup;
use App\Models\PbxTrunk;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PbxEnterpriseDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles
        $adminRole = Role::firstOrCreate(
            ['slug' => 'super-administrator'],
            [
                'name'        => 'Super Administrator',
                'description' => 'Full administrative access across all telephony, routing, and system modules.',
                'is_super'    => true,
            ]
        );
        $supervisorRole = Role::firstOrCreate(
            ['slug' => 'telecom-supervisor'],
            [
                'name'        => 'Telecom Supervisor',
                'description' => 'Real-time call monitoring, ChanSpy coaching (Listen, Whisper, Barge), and CDR analytics.',
                'is_super'    => false,
            ]
        );
        $agentRole = Role::firstOrCreate(
            ['slug' => 'pbx-agent'],
            [
                'name'        => 'PBX Call Agent',
                'description' => 'Inbound customer handling, outbound dialing, softphone WebRTC access.',
                'is_super'    => false,
            ]
        );

        // 2. Staff Employees & Extension Users
        $staffData = [
            ['name' => 'Sarah Jenkins', 'email' => 'sarah@voxpulse.com', 'ext' => '101', 'title' => 'Chief Executive Officer', 'role' => $adminRole->id],
            ['name' => 'Michael Chang', 'email' => 'michael@voxpulse.com', 'ext' => '102', 'title' => 'Chief Technology Officer', 'role' => $supervisorRole->id],
            ['name' => 'David Miller', 'email' => 'david@voxpulse.com', 'ext' => '103', 'title' => 'Head of Global Sales', 'role' => $supervisorRole->id],
            ['name' => 'Jessica Taylor', 'email' => 'jessica@voxpulse.com', 'ext' => '104', 'title' => 'Senior Support Engineer', 'role' => $agentRole->id],
            ['name' => 'Ahmed Al-Mansoor', 'email' => 'ahmed@voxpulse.com', 'ext' => '105', 'title' => 'Enterprise Account Executive', 'role' => $agentRole->id],
            ['name' => 'Elena Rostova', 'email' => 'elena@voxpulse.com', 'ext' => '106', 'title' => 'Operations & Billing Director', 'role' => $agentRole->id],
            ['name' => 'Liam O\'Connor', 'email' => 'liam@voxpulse.com', 'ext' => '107', 'title' => 'Customer Success Specialist', 'role' => $agentRole->id],
            ['name' => 'Priya Sharma', 'email' => 'priya@voxpulse.com', 'ext' => '108', 'title' => 'Technical Support Specialist', 'role' => $agentRole->id],
        ];

        foreach ($staffData as $item) {
            $user = User::firstOrCreate(
                ['email' => $item['email']],
                [
                    'name'     => $item['name'],
                    'password' => Hash::make('VoxPulse@2026!'),
                    'type'     => 'admin',
                ]
            );
            $user->roles()->syncWithoutDetaching([$item['role']]);

            PbxExtension::updateOrCreate(
                ['extension' => $item['ext']],
                [
                    'name'     => $item['name'] . ' (' . $item['title'] . ')',
                    'user_id'  => $user->id,
                    'password' => 'SipKey_' . $item['ext'] . '_2026',
                    'enabled'  => true,
                ]
            );
        }

        // 3. Carrier SIP Trunks
        $trunkTwilio = PbxTrunk::updateOrCreate(
            ['name' => 'Twilio US Voice Gateway'],
            [
                'type'                => 'pjsip_registration',
                'host'                => 'voxpulse-trunk.pstn.twilio.com',
                'port'                => 5060,
                'transport'           => 'udp',
                'username'            => 'AC71902834b92',
                'secret'              => 'TwilioSecret_Vox99',
                'outbound_caller_id'  => '+18005550199',
                'caller_name'         => 'VoxPulse Corporate',
                'max_channels'        => 30,
                'codecs'              => 'ulaw,alaw,opus,g729',
                'is_registration'     => true,
                'is_active'           => true,
                'notes'               => 'Primary North American toll-free carrier termination trunk.',
            ]
        );

        $trunkTelnyx = PbxTrunk::updateOrCreate(
            ['name' => 'Telnyx Global SIP Carrier'],
            [
                'type'                => 'pjsip_registration',
                'host'                => 'sip.telnyx.com',
                'port'                => 5060,
                'transport'           => 'tls',
                'username'            => 'telnyx_vox_core',
                'secret'              => 'TelnyxSecret_2026',
                'outbound_caller_id'  => '+18884321000',
                'caller_name'         => 'VoxPulse Global',
                'max_channels'        => 50,
                'codecs'              => 'opus,ulaw,alaw,g729',
                'is_registration'     => true,
                'is_active'           => true,
                'notes'               => 'High-capacity SIP carrier for international terminations & VIP DIDs.',
            ]
        );

        $trunkBtcl = PbxTrunk::updateOrCreate(
            ['name' => 'BTCL Telecom Bangladesh Gateway'],
            [
                'type'                => 'pjsip_ip',
                'host'                => '103.107.160.1',
                'port'                => 5060,
                'transport'           => 'udp',
                'username'            => 'btcl_trunk_dhaka',
                'secret'              => 'BtclPass_880',
                'outbound_caller_id'  => '+88029876543',
                'caller_name'         => 'VoxPulse Bangladesh',
                'max_channels'        => 20,
                'codecs'              => 'alaw,ulaw',
                'is_registration'     => false,
                'is_active'           => true,
                'notes'               => 'Direct IP interconnect for Bangladesh domestic mobile (01X) traffic.',
            ]
        );

        // 4. Ring Groups (Department Hunt Groups)
        $rgSales = PbxRingGroup::updateOrCreate(
            ['extension' => '600'],
            [
                'name'                 => 'Enterprise Sales Team',
                'strategy'             => 'ringall',
                'ring_time'            => 25,
                'members'              => ['103', '105'],
                'failover_type'        => 'ivr',
                'failover_destination' => '500',
                'is_active'            => true,
                'description'          => 'Simultaneously rings all active Sales Executives. Falls back to Main IVR.',
            ]
        );

        $rgSupport = PbxRingGroup::updateOrCreate(
            ['extension' => '601'],
            [
                'name'                 => 'Customer Support Desk',
                'strategy'             => 'hunt',
                'ring_time'            => 30,
                'members'              => ['104', '107', '108'],
                'failover_type'        => 'extension',
                'failover_destination' => '101',
                'is_active'            => true,
                'description'          => 'Sequential hunt group across tier-1 & tier-2 support engineers.',
            ]
        );

        $rgEscalations = PbxRingGroup::updateOrCreate(
            ['extension' => '602'],
            [
                'name'                 => 'Executive Escalations',
                'strategy'             => 'ringall',
                'ring_time'            => 20,
                'members'              => ['101', '102', '106'],
                'failover_type'        => 'extension',
                'failover_destination' => '101',
                'is_active'            => true,
                'description'          => 'High-priority executive escalation team.',
            ]
        );

        // 5. Digital Receptionists (IVR)
        $ivrMain = PbxIvr::updateOrCreate(
            ['extension' => '500'],
            [
                'name'                => 'Main Corporate Digital Receptionist',
                'description'         => 'Welcome to VoxPulse Enterprise. Main automated multi-level greeting receptionist.',
                'greeting_message'    => 'Thank you for calling VoxPulse. Press 1 for Sales, 2 for Technical Support, 3 for Billing & Operations, or stay on the line for the operator.',
                'sound_prompt'        => 'beep',
                'timeout'             => 10,
                'timeout_destination' => '101',
                'invalid_destination' => '101',
                'is_active'           => true,
            ]
        );

        PbxIvrOption::where('ivr_id', $ivrMain->id)->delete();
        $ivrMainOptions = [
            ['digit' => '1', 'destination' => '600', 'label' => 'Enterprise Sales Team'],
            ['digit' => '2', 'destination' => '601', 'label' => 'Technical Support Desk'],
            ['digit' => '3', 'destination' => '106', 'label' => 'Billing & Operations (Elena Rostova)'],
            ['digit' => '0', 'destination' => '101', 'label' => 'Operator Desk (Sarah Jenkins)'],
        ];
        foreach ($ivrMainOptions as $opt) {
            PbxIvrOption::create([
                'ivr_id'           => $ivrMain->id,
                'digit'            => $opt['digit'],
                'destination_type' => 'extension',
                'destination'      => $opt['destination'],
                'label'            => $opt['label'],
            ]);
        }

        $ivrAfterHours = PbxIvr::updateOrCreate(
            ['extension' => '501'],
            [
                'name'                => 'After-Hours Emergency Support IVR',
                'description'         => 'Automated night receptionist active outside standard operating hours.',
                'greeting_message'    => 'You have reached VoxPulse after regular office hours. Press 1 for Emergency On-Call Engineer, or hold to leave a voicemail.',
                'sound_prompt'        => 'beep',
                'timeout'             => 12,
                'timeout_destination' => '104',
                'invalid_destination' => '104',
                'is_active'           => true,
            ]
        );

        PbxIvrOption::where('ivr_id', $ivrAfterHours->id)->delete();
        PbxIvrOption::create([
            'ivr_id'           => $ivrAfterHours->id,
            'digit'            => '1',
            'destination_type' => 'extension',
            'destination'      => '104',
            'label'            => 'On-Call Engineer (Jessica Taylor)',
        ]);

        // 6. Inbound DID Routes
        $inboundData = [
            [
                'name'               => 'Primary Toll-Free DID -> Main IVR',
                'did_pattern'        => '+18005550199',
                'caller_id_pattern'  => 'ANY',
                'destination_type'   => 'ivr',
                'destination_id'     => '500',
                'cid_prefix'         => '[MAIN] ',
                'priority'           => 1,
                'is_active'          => true,
                'notes'              => 'Inbound traffic from company primary 1-800 number routed to IVR.',
            ],
            [
                'name'               => 'VIP Direct Support Hotline',
                'did_pattern'        => '+18884321000',
                'caller_id_pattern'  => 'ANY',
                'destination_type'   => 'ring_group',
                'destination_id'     => '601',
                'cid_prefix'         => '[VIP-SUPPORT] ',
                'priority'           => 2,
                'is_active'          => true,
                'notes'              => 'Priority line bypassing IVR directly to support engineering team.',
            ],
            [
                'name'               => 'Dhaka Corporate Office Line',
                'did_pattern'        => '+88029876543',
                'caller_id_pattern'  => 'ANY',
                'destination_type'   => 'ring_group',
                'destination_id'     => '600',
                'cid_prefix'         => '[DHAKA-SALES] ',
                'priority'           => 3,
                'is_active'          => true,
                'notes'              => 'Direct inbound calls from Bangladesh local PSTN.',
            ],
            [
                'name'               => 'Global Catch-All Fallback Route',
                'did_pattern'        => 'ANY',
                'caller_id_pattern'  => 'ANY',
                'destination_type'   => 'ivr',
                'destination_id'     => '500',
                'cid_prefix'         => '[INBOUND] ',
                'priority'           => 10,
                'is_active'          => true,
                'notes'              => 'Catch-all route ensuring no incoming call drops.',
            ],
        ];

        foreach ($inboundData as $in) {
            PbxInboundRoute::updateOrCreate(['name' => $in['name']], $in);
        }

        // 7. Outbound Dial Rules
        $outboundData = [
            [
                'name'                => 'Bangladesh Mobile & National (01X)',
                'dial_pattern'        => '_01XXXXXXXXX',
                'strip_digits'        => 0,
                'prepend_digits'      => '+880',
                'primary_trunk_id'    => $trunkBtcl->id,
                'failover_trunk_id'   => $trunkTelnyx->id,
                'caller_id_override'  => '+88029876543',
                'priority'            => 1,
                'is_active'           => true,
                'notes'               => 'Routes 11-digit Bangladesh mobile numbers via BTCL gateway with Telnyx failover.',
            ],
            [
                'name'                => 'North America Domestic (10-Digit)',
                'dial_pattern'        => '_NXXNXXXXXX',
                'strip_digits'        => 0,
                'prepend_digits'      => '+1',
                'primary_trunk_id'    => $trunkTwilio->id,
                'failover_trunk_id'   => $trunkTelnyx->id,
                'caller_id_override'  => '+18005550199',
                'priority'            => 2,
                'is_active'           => true,
                'notes'               => 'Standard NANP numbers routed through primary Twilio trunk.',
            ],
            [
                'name'                => 'International E.164 (+Prefix)',
                'dial_pattern'        => '_+X.',
                'strip_digits'        => 0,
                'prepend_digits'      => null,
                'primary_trunk_id'    => $trunkTelnyx->id,
                'failover_trunk_id'   => $trunkTwilio->id,
                'caller_id_override'  => '+18884321000',
                'priority'            => 3,
                'is_active'           => true,
                'notes'               => 'Global international calls routed via Telnyx tier-1 carrier.',
            ],
            [
                'name'                => 'Emergency Services (999 / 911)',
                'dial_pattern'        => '_9X.',
                'strip_digits'        => 0,
                'prepend_digits'      => null,
                'primary_trunk_id'    => $trunkBtcl->id,
                'failover_trunk_id'   => null,
                'caller_id_override'  => '+88029876543',
                'priority'            => 4,
                'is_active'           => true,
                'notes'               => 'Emergency direct trunk dispatch without delay.',
            ],
        ];

        foreach ($outboundData as $out) {
            PbxOutboundRoute::updateOrCreate(['name' => $out['name']], $out);
        }

        // 8. Realistic CDR Call History (35 Entries)
        if (PbxCallLog::count() < 10) {
            $cdrSamples = [
                ['caller' => '+14155552671', 'callee' => '500', 'type' => 'inbound', 'disposition' => 'ANSWERED', 'dur' => 245, 'minutes_ago' => 14],
                ['caller' => '101', 'callee' => '103', 'type' => 'internal', 'disposition' => 'ANSWERED', 'dur' => 182, 'minutes_ago' => 38],
                ['caller' => '+8801711223344', 'callee' => '601', 'type' => 'inbound', 'disposition' => 'ANSWERED', 'dur' => 420, 'minutes_ago' => 65],
                ['caller' => '104', 'callee' => '01719876543', 'type' => 'outbound', 'disposition' => 'ANSWERED', 'dur' => 95, 'minutes_ago' => 92],
                ['caller' => '+12125557890', 'callee' => '600', 'type' => 'inbound', 'disposition' => 'NO ANSWER', 'dur' => 25, 'minutes_ago' => 120],
                ['caller' => '103', 'callee' => '+13125551234', 'type' => 'outbound', 'disposition' => 'ANSWERED', 'dur' => 512, 'minutes_ago' => 155],
                ['caller' => '105', 'callee' => '106', 'type' => 'internal', 'disposition' => 'ANSWERED', 'dur' => 74, 'minutes_ago' => 190],
                ['caller' => '+8801819283746', 'callee' => '500', 'type' => 'inbound', 'disposition' => 'ANSWERED', 'dur' => 310, 'minutes_ago' => 230],
                ['caller' => '107', 'callee' => '104', 'type' => 'internal', 'disposition' => 'BUSY', 'dur' => 12, 'minutes_ago' => 275],
                ['caller' => '101', 'callee' => '999', 'type' => 'internal', 'disposition' => 'ANSWERED', 'dur' => 45, 'minutes_ago' => 310],
                ['caller' => '+16505559876', 'callee' => '601', 'type' => 'inbound', 'disposition' => 'ANSWERED', 'dur' => 198, 'minutes_ago' => 360],
                ['caller' => '108', 'callee' => '01811223344', 'type' => 'outbound', 'disposition' => 'ANSWERED', 'dur' => 160, 'minutes_ago' => 420],
                ['caller' => '+8801912345678', 'callee' => '500', 'type' => 'inbound', 'disposition' => 'ANSWERED', 'dur' => 280, 'minutes_ago' => 480],
                ['caller' => '102', 'callee' => '101', 'type' => 'internal', 'disposition' => 'ANSWERED', 'dur' => 340, 'minutes_ago' => 550],
                ['caller' => '+14085551122', 'callee' => '600', 'type' => 'inbound', 'disposition' => 'NO ANSWER', 'dur' => 22, 'minutes_ago' => 610],
                ['caller' => '104', 'callee' => '108', 'type' => 'internal', 'disposition' => 'ANSWERED', 'dur' => 88, 'minutes_ago' => 700],
                ['caller' => '103', 'callee' => '+14155550099', 'type' => 'outbound', 'disposition' => 'ANSWERED', 'dur' => 415, 'minutes_ago' => 820],
                ['caller' => '+8801755667788', 'callee' => '601', 'type' => 'inbound', 'disposition' => 'ANSWERED', 'dur' => 230, 'minutes_ago' => 960],
                ['caller' => '106', 'callee' => '101', 'type' => 'internal', 'disposition' => 'ANSWERED', 'dur' => 110, 'minutes_ago' => 1100],
                ['caller' => '105', 'callee' => '01611223344', 'type' => 'outbound', 'disposition' => 'ANSWERED', 'dur' => 320, 'minutes_ago' => 1250],
            ];

            foreach ($cdrSamples as $s) {
                $startedAt = Carbon::now()->subMinutes($s['minutes_ago']);
                $endedAt   = (clone $startedAt)->addSeconds($s['dur']);

                PbxCallLog::create([
                    'caller'      => $s['caller'],
                    'caller_name' => $s['caller'],
                    'callee'      => $s['callee'],
                    'callee_name' => $s['callee'],
                    'direction'   => $s['type'],
                    'status'      => strtolower(str_replace(' ', '_', $s['disposition'])),
                    'duration'    => $s['dur'],
                    'sip_call_id' => 'SIP-' . bin2hex(random_bytes(8)),
                    'started_at'  => $startedAt,
                    'ended_at'    => $endedAt,
                ]);
            }
        }
    }
}
