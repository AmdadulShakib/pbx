<?php

namespace Database\Seeders;

use App\Models\PbxExtension;
use App\Models\PbxHotDesk;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PbxHotDeskSeeder extends Seeder
{
    public function run(): void
    {
        $ext101 = PbxExtension::where('extension', '101')->first();
        $ext102 = PbxExtension::where('extension', '102')->first();
        $ext106 = PbxExtension::where('extension', '106')->first();

        $stations = [
            [
                'name'                 => 'Sales Floor - Desk 01',
                'station_endpoint'     => 'desk-sales-01',
                'device_model'         => 'Yealink SIP-T46U',
                'mac_address'          => '00:15:65:8a:bc:12',
                'ip_address'           => '192.168.1.101',
                'location'             => 'Floor 2 - Sales Wing (Pod A)',
                'current_extension_id' => $ext101?->id,
                'current_extension'    => $ext101 ? '101' : null,
                'current_user_name'    => $ext101?->user?->name ?? 'Sarah Jenkins',
                'pin'                  => '1234',
                'status'               => $ext101 ? 'occupied' : 'available',
                'logged_in_at'         => $ext101 ? Carbon::now()->subHours(3) : null,
                'notes'                => 'Dual Gigabit ports with PoE. Assigned to revolving shift sales agents.',
            ],
            [
                'name'                 => 'Support Pod - Station A',
                'station_endpoint'     => 'desk-support-01',
                'device_model'         => 'Grandstream GXP2170',
                'mac_address'          => '00:0b:82:76:54:32',
                'ip_address'           => '192.168.1.102',
                'location'             => 'Floor 1 - Technical Support Center',
                'current_extension_id' => $ext102?->id,
                'current_extension'    => $ext102 ? '102' : null,
                'current_user_name'    => $ext102?->user?->name ?? 'Michael Chang',
                'pin'                  => '1234',
                'status'               => $ext102 ? 'occupied' : 'available',
                'logged_in_at'         => $ext102 ? Carbon::now()->subHours(2) : null,
                'notes'                => 'High-volume Tier 2 support desk with Bluetooth headset integration.',
            ],
            [
                'name'                 => 'Billing & Operations Desk',
                'station_endpoint'     => 'desk-billing-01',
                'device_model'         => 'Yealink SIP-T43U',
                'mac_address'          => '00:15:65:9c:df:45',
                'ip_address'           => '192.168.1.106',
                'location'             => 'Floor 2 - Finance & Ops Wing',
                'current_extension_id' => $ext106?->id,
                'current_extension'    => $ext106 ? '106' : null,
                'current_user_name'    => $ext106?->user?->name ?? 'Elena Rostova',
                'pin'                  => '1234',
                'status'               => $ext106 ? 'occupied' : 'available',
                'logged_in_at'         => $ext106 ? Carbon::now()->subHours(1) : null,
                'notes'                => 'Dedicated accounting & telecom provisioning station.',
            ],
            [
                'name'                 => 'Executive Roaming Workstation',
                'station_endpoint'     => 'desk-exec-roam',
                'device_model'         => 'Polycom VVX 450',
                'mac_address'          => '00:04:f2:33:44:55',
                'ip_address'           => '192.168.1.108',
                'location'             => 'Floor 3 - Executive Conference Suite',
                'current_extension_id' => null,
                'current_extension'    => null,
                'current_user_name'    => null,
                'pin'                  => '9988',
                'status'               => 'available',
                'logged_in_at'         => null,
                'notes'                => 'Roaming workstation for visiting C-level executives and partners.',
            ],
            [
                'name'                 => 'Flex Desk - Workstation 05',
                'station_endpoint'     => 'desk-flex-05',
                'device_model'         => 'Fanvil X4U Pro',
                'mac_address'          => '0c:38:bb:21:43:65',
                'ip_address'           => '192.168.1.110',
                'location'             => 'Floor 1 - Open Coworking Commons',
                'current_extension_id' => null,
                'current_extension'    => null,
                'current_user_name'    => null,
                'pin'                  => '1234',
                'status'               => 'available',
                'logged_in_at'         => null,
                'notes'                => 'Open seating desk for remote staff visiting headquarters.',
            ],
        ];

        foreach ($stations as $data) {
            PbxHotDesk::updateOrCreate(
                ['station_endpoint' => $data['station_endpoint']],
                $data
            );
        }
    }
}
