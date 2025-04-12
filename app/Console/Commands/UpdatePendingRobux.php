<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Group;
use App\Models\PendingRobux;

class UpdatePendingRobux extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-pending-robux';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $groups = Group::all();
        foreach($groups as $group){
            $robloxAccount = $group->robloxUsers->where('user_type','owner')->first();
            $cookie = $robloxAccount->cookie;
            $group_id = $group->group_id;

            $datas = $this->getPendingRobuxData($group_id, $cookie);
            if($datas){
                foreach($datas['transactions'] as $data){
                    $robux = $data['total_robux'];
                    $arrivalDate = $data['robux_arrival_date'];
                    PendingRobux::updateOrCreate(
                        ['agent_id' => $data['agent_id']],  // ค้นหาจาก agent_id เท่านั้น
                        [
                            'group_id' => $group_id,         // ฟิลด์ที่จะอัปเดตหรือสร้างใหม่
                            'amount' => $robux,
                            'arrival_date' => $arrivalDate,
                        ]
                    );
                }
            }

        }
    }

    /**
     * ดึงข้อมูลธุรกรรมจาก Roblox API
     *
     * @param string $groupId รหัสกลุ่ม Roblox
     * @param string $cookie คุกกี้ .ROBLOSECURITY
     * @return array|null ข้อมูลธุรกรรมหรือ null ถ้าเกิดข้อผิดพลาด
     */
    public function getRobloxTransactions(string $groupId, string $cookie): ?array
    {
        $url = "https://economy.roblox.com/v2/groups/$groupId/transactions?transactionType=Sale&sortOrder=Asc&limit=100";

        $headers = [
            "Cookie: .ROBLOSECURITY=$cookie",
            "Accept: application/json",
            "User-Agent: Mozilla/5.0"
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            Log::error("Curl error: " . curl_error($ch));
        }

        curl_close($ch);

        if ($httpCode !== 200) {
            Log::error("Roblox API Error: HTTP Code $httpCode");
            return null;
        }

        return json_decode($response, true);
    }

    /**
     * ประมวลผลข้อมูลธุรกรรมและจัดรูปแบบสำหรับบันทึกลงฐานข้อมูล
     *
     * @param array $transactions ข้อมูลธุรกรรมจาก API
     * @return array|null ข้อมูลที่พร้อมบันทึกลงฐานข้อมูลหรือ null ถ้าเกิดข้อผิดพลาด
     */
    public function processTransactions(array $transactions): ?array
    {
        $agents = [];
        $totalPendingRobux = 0;

        try {
            foreach ($transactions['data'] as $transaction) {
                if ($transaction['isPending']) {
                    $agentId = $transaction['agent']['id'];
                    $amount = $transaction['currency']['amount'];
                    $createdDate = new DateTime($transaction['created'], new DateTimeZone('UTC'));

                    if (!isset($agents[$agentId])) {
                        $agents[$agentId] = [
                            'agent_id' => $agentId,
                            'agent_name' => $transaction['agent']['name'] ?? null,
                            'total_robux' => 0,
                            'latest_date' => $createdDate,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    $agents[$agentId]['total_robux'] += $amount;
                    $totalPendingRobux += $amount;

                    if ($createdDate > $agents[$agentId]['latest_date']) {
                        $agents[$agentId]['latest_date'] = $createdDate;
                    }
                }
            }

            foreach ($agents as &$agent) {
                $arrivalDate = clone $agent['latest_date'];
                $arrivalDate->modify('+5 days')->setTimezone(new DateTimeZone('Asia/Bangkok'));

                $agent['robux_arrival_date'] = $arrivalDate->format('Y-m-d H:i:s');
                unset($agent['latest_date']);
            }

            return [
                "total_robux" => $totalPendingRobux,
                "transactions" => array_values($agents)
            ];
        } catch (Exception $e) {
            Log::error("Error processing transactions: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ดึงและประมวลผลธุรกรรมทั้งหมด
     *
     * @param string $groupId รหัสกลุ่ม Roblox
     * @param string $cookie คุกกี้ .ROBLOSECURITY
     * @return array|null ผลลัพธ์การประมวลผลหรือ null ถ้าเกิดข้อผิดพลาด
     */
    public function getPendingRobuxData(string $groupId, string $cookie): ?array
    {
        try {
            $transactions = $this->getRobloxTransactions($groupId, $cookie);

            if ($transactions && isset($transactions['data'])) {
                return $this->processTransactions($transactions);
            }
            return null;
            // return [
            //     'total_robux' => 500,
            //     'transactions' => [
            //         [
            //             "agent_id" => 12345678,
            //             "agent_name" => "TestUser1",
            //             "total_robux" => 300,
            //             "robux_arrival_date" => "2025-04-18 09:12:34",
            //             "created_at" => now(),
            //             "updated_at" => now()
            //         ],
            //         [
            //             "agent_id" => 87654321,
            //             "agent_name" => "TestUser2",
            //             "total_robux" => 700,
            //             "robux_arrival_date" => "2025-04-19 15:30:00",
            //             "created_at" => now(),
            //             "updated_at" => now()
            //         ]
            //     ]
            // ];
        } catch (Exception $e) {
            Log::error("Fatal Error in getPendingRobuxData: " . $e->getMessage());
            return null;
        }
    }
}
