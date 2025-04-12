<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Group;


class UpdateGroupFund extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-group-fund';

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
        try{
            $groups = Group::all();
            foreach($groups as $group){
                $robloxUsers = $group->robloxUsers->where('user_type', 'owner')->first();
                $cookie = $robloxUsers['cookie'];
                $group_id = $group['group_id'];
                // เริ่มการเรียก API เพื่อดึงข้อมูล robux
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, "https://economy.roblox.com/v1/groups/$group_id/currency");
                curl_setopt($curl, CURLOPT_COOKIE, ".ROBLOSECURITY=$cookie");
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($curl);

                if (curl_errno($curl)) {
                    curl_close($curl);
                    return json_encode(array(
                        "status" => "error",
                        "message" => 'cURL error: ' . curl_error($curl)
                    ));
                }

                curl_close($curl);
                $response = json_decode($response, true);

                if (isset($response['robux'])) {
                    $robux = $response['robux'];
                    $group->where('group_id',$group_id)->update([
                        'robux' => $robux,
                    ]);

                    $this->info(json_encode(array(
                    "status" => "success",
                    "robux" => $robux
                )));
                } else {
                $this->error(json_encode(array(
                    "status" => "error",
                    "message" => "Failed to retrieve robux data."
                )));
                }
            }

     } catch (Exception $e) {
         $this->error(json_encode(array(
            "status" => "error",
            "message" => $e->getMessage()
        )));
     }
    }
}
