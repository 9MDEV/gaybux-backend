<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RobloxUser;
use Exception;
use Illuminate\Support\Facades\Http;

class UpdateCookie extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-cookie';

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
        $RobloxUsers = RobloxUser::all();
        foreach($RobloxUsers as $user){
            $cookie = $this->start_process($user['cookie']);
            RobloxUser::where('roblox_user_id', $user['roblox_user_id'])->update([
                'cookie' => $cookie,
            ]);
        }

        $this->info('Cookie update process completed.');

    }

    function start_process($cookie) {
        $xcsrf_token = $this->get_c($cookie);
        $rbx_authentication_ticket = $this->get_rbx_authentication_ticket($cookie, $xcsrf_token);
        return $this->get_set_cookie($cookie, $rbx_authentication_ticket);
    }

    function get_c($cookie) {
        $ch = curl_init("https://auth.roblox.com/v2/logout");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Cookie: .ROBLOSECURITY=" . $cookie
        ]);
        curl_setopt($ch, CURLOPT_HEADER, true); // Include header in output
        curl_setopt($ch, CURLOPT_VERBOSE, false); // Disable verbose output
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); // Ensure the correct method
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        curl_close($ch);

        // Extract CSRF token from header
        if (preg_match('/x-csrf-token: (.+)/i', $header, $matches)) {
            return trim($matches[1]);
        } else {
            throw new Exception("CSRF token not found in response headers.");
        }
    }

    function get_rbx_authentication_ticket($cookie, $xcsrf_token) {
        $ch = curl_init("https://auth.roblox.com/v1/authentication-ticket");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "rbxauthenticationnegotiation: 1",
            "referer: https://www.roblox.com/camel",
            "Content-Type: application/json",
            "x-csrf-token: " . $xcsrf_token,
            "Cookie: .ROBLOSECURITY=" . $cookie
        ]);
        curl_setopt($ch, CURLOPT_HEADER, true); // Include header in output
        curl_setopt($ch, CURLOPT_VERBOSE, false); // Disable verbose output
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        curl_close($ch);

        // Extract authentication ticket from header
        if (preg_match('/rbx-authentication-ticket: (.+)/i', $header, $matches)) {
            return trim($matches[1]);
        } else {
            throw new Exception("Authentication ticket not found in response headers.");
        }
    }

    function get_set_cookie($cookie, $rbx_authentication_ticket) {
        $ch = curl_init("https://auth.roblox.com/v1/authentication-ticket/redeem");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "rbxauthenticationnegotiation: 1",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "authenticationTicket" => $rbx_authentication_ticket
        ]));
        curl_setopt($ch, CURLOPT_HEADER, true); // Include header in output
        curl_setopt($ch, CURLOPT_VERBOSE, false); // Disable verbose output
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        curl_close($ch);

        // Extract set-cookie value
        if (preg_match('/set-cookie:.*\.ROBLOSECURITY=([^;]+)/i', $header, $matches)) {
            return $matches[1];
        } else {
            throw new Exception("Set-cookie value not found in response headers.");
        }
    }
}
