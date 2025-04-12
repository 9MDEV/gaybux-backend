<?php

namespace App\Services;

use OTPHP\TOTP;
use Exception;
use Illuminate\Support\Facades\Http;

class RobuxService
{
    private const AUTH_ENDPOINT = 'https://auth.roblox.com';
    private const GROUPS_ENDPOINT = 'https://groups.roblox.com';
    private const USERS_ENDPOINT = 'https://users.roblox.com';
    private const VERIFICATION_ENDPOINT = 'https://twostepverification.roblox.com';
    private const CHALLENGE_ENDPOINT = 'https://apis.roblox.com/challenge/v1';

    /**
     * Generate OTP using TOTP
     */
    private function generateOTP($secretKey)
    {
        $totp = TOTP::create($secretKey);
        return $totp->now();
    }

    /**
     * Send a POST request and get headers and body
     */
    private function sendPostRequest($url, $data, $headers)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => true
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        $info = curl_getinfo($ch);
        $header = substr($response, 0, $info['header_size']);
        $body = substr($response, $info['header_size']);
        curl_close($ch);

        return ['header' => $header, 'body' => $body];
    }

    /**
     * Send a GET request
     */
    private function sendGetRequest($url)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        curl_close($ch);
        return $response;
    }

    /**
     * Extract headers from header text
     */
    private function extractHeaders($headerText)
    {
        $headers = [];
        foreach (explode("\r\n", $headerText) as $line) {
            if (strpos($line, ':') !== false) {
                [$key, $value] = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        return $headers;
    }

    /**
     * Get CSRF token from Roblox
     */
    private function getCSRFToken($robloxKey)
    {
        $loginUrl = self::AUTH_ENDPOINT . '/v2/logout';
        $csrfHeaders = [
            "Accept: application/json",
            "Cookie: .ROBLOSECURITY=$robloxKey"
        ];

        $ch = curl_init($loginUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $csrfHeaders,
            CURLOPT_HEADER => true,
            CURLOPT_CUSTOMREQUEST => "POST"
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        curl_close($ch);

        if (preg_match('/x-csrf-token: (.+)/i', $header, $matches)) {
            return trim($matches[1]);
        } else {
            throw new Exception("CSRF token not found in response headers.");
        }
    }

    /**
     * Handle payout challenge
     */
    private function handlePayoutChallenge($groupId, $userId, $amount, $robloxKey, $csrfToken)
    {
        $url = self::GROUPS_ENDPOINT . "/v1/groups/$groupId/payouts";
        $headers = [
            "Accept: application/json",
            "Cookie: .ROBLOSECURITY=$robloxKey",
            "X-CSRF-TOKEN: $csrfToken",
            "Content-Type: application/json"
        ];

        $payoutData = [
            "PayoutType" => 1,
            "Recipients" => [
                [
                    "recipientId" => $userId,
                    "recipientType" => 0,
                    "amount" => $amount
                ]
            ]
        ];

        $response = $this->sendPostRequest($url, $payoutData, $headers);
        $responseHeaders = $this->extractHeaders($response['header']);

        if (isset($responseHeaders['rblx-challenge-metadata']) && isset($responseHeaders['rblx-challenge-id'])) {
            $challengeMetadata = json_decode(base64_decode($responseHeaders['rblx-challenge-metadata']), true);
            return [$challengeMetadata['challengeId'], $responseHeaders['rblx-challenge-id']];
        } else {
            throw new Exception('Failed to retrieve challenge metadata.');
        }
    }

    /**
     * Verify challenge with 2FA code
     */
    private function verifyChallenge($userId, $challengeId, $twoFactorCode, $csrfToken, $robloxKey)
    {
        $url = self::VERIFICATION_ENDPOINT . "/v1/users/$userId/challenges/authenticator/verify";
        $data = [
            'challengeId' => $challengeId,
            'actionType' => 'Generic',
            'code' => $twoFactorCode
        ];

        $headers = [
            "Accept: application/json",
            "Cookie: .ROBLOSECURITY=$robloxKey",
            "X-CSRF-TOKEN: $csrfToken",
            "Content-Type: application/json"
        ];

        $response = $this->sendPostRequest($url, $data, $headers);
        $responseJson = json_decode($response['body'], true);

        if (isset($responseJson['verificationToken'])) {
            return $responseJson['verificationToken'];
        } else {
            throw new Exception('Failed to verify challenge.');
        }
    }

    /**
     * Continue the challenge process
     */
    private function continueChallenge($challengeId, $rblxChallengeId, $verificationToken, $csrfToken, $robloxKey)
    {
        $url = self::CHALLENGE_ENDPOINT . "/continue";
        $challengeMetadata = [
            'verificationToken' => $verificationToken,
            'rememberDevice' => false,
            'challengeId' => $challengeId,
            'actionType' => 'Generic'
        ];

        $data = [
            'challengeId' => $rblxChallengeId,
            'challengeType' => 'twostepverification',
            'challengeMetadata' => json_encode($challengeMetadata)
        ];

        $headers = [
            "Accept: application/json",
            "Cookie: .ROBLOSECURITY=$robloxKey",
            "X-CSRF-TOKEN: $csrfToken",
            "Content-Type: application/json"
        ];

        $this->sendPostRequest($url, $data, $headers);
    }

    /**
     * Finalize payout process
     */
    private function finalizePayout($groupId, $userId, $amount, $rblxChallengeId, $challengeMetadata, $csrfToken, $robloxKey)
    {
        $url = self::GROUPS_ENDPOINT . "/v1/groups/$groupId/payouts";
        $headers = [
            "Accept: application/json",
            "Cookie: .ROBLOSECURITY=$robloxKey",
            "X-CSRF-TOKEN: $csrfToken",
            "Content-Type: application/json",
            "rblx-challenge-id: $rblxChallengeId",
            "rblx-challenge-type: twostepverification",
            "rblx-challenge-metadata: " . base64_encode(json_encode($challengeMetadata))
        ];

        $payoutData = [
            "PayoutType" => 1,
            "Recipients" => [
                [
                    "recipientId" => $userId,
                    "recipientType" => 0,
                    "amount" => $amount
                ]
            ]
        ];

        $response = $this->sendPostRequest($url, $payoutData, $headers);
        return $response['body'];
    }

    /**
     * Get authentication ticket
     */
    private function getRbxAuthenticationTicket($cookie, $xcsrfToken)
    {
        $url = self::AUTH_ENDPOINT . "/v1/authentication-ticket";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "rbxauthenticationnegotiation: 1",
                "referer: https://www.roblox.com/camel",
                "Content-Type: application/json",
                "x-csrf-token: " . $xcsrfToken,
                "Cookie: .ROBLOSECURITY=" . $cookie
            ],
            CURLOPT_HEADER => true
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        curl_close($ch);

        if (preg_match('/rbx-authentication-ticket: (.+)/i', $header, $matches)) {
            return trim($matches[1]);
        } else {
            throw new Exception("Authentication ticket not found in response headers.");
        }
    }

    /**
     * Redeem authentication ticket to get new cookie
     */
    private function redeemAuthenticationTicket($rbxAuthenticationTicket)
    {
        $url = self::AUTH_ENDPOINT . "/v1/authentication-ticket/redeem";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "rbxauthenticationnegotiation: 1",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode([
                "authenticationTicket" => $rbxAuthenticationTicket
            ]),
            CURLOPT_HEADER => true
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        curl_close($ch);

        if (preg_match('/set-cookie:.*\.ROBLOSECURITY=([^;]+)/i', $header, $matches)) {
            return $matches[1];
        } else {
            throw new Exception("Set-cookie value not found in response headers.");
        }
    }

    /**
     * Start authentication process to get a fresh cookie
     */
    private function refreshCookie($cookie)
    {
        $xcsrfToken = $this->getCSRFToken($cookie);
        $rbxAuthenticationTicket = $this->getRbxAuthenticationTicket($cookie, $xcsrfToken);
        return $this->redeemAuthenticationTicket($rbxAuthenticationTicket);
    }

    /**
     * Check if user is in the specified group
     */
    private function checkUserInGroup($groupId, $userId)
    {
        $url = self::GROUPS_ENDPOINT . "/v2/users/$userId/groups/roles?includeLocked=false&includeNotificationPreferences=false";
        $response = $this->sendGetRequest($url);
        $data = json_decode($response, true);

        if (!isset($data['data']) || !is_array($data['data'])) {
            return false;
        }

        $searchGroupId = (int)$groupId;

        foreach ($data['data'] as $item) {
            if (isset($item['group']['id']) && $item['group']['id'] === $searchGroupId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get user ID by username
     */
    private function getUserIdByUsername($username)
    {
        $url = self::USERS_ENDPOINT . "/v1/usernames/users";
        $postData = json_encode([
            "usernames" => [$username],
            "excludeBannedUsers" => true
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData)
            ],
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("cURL error: " . curl_error($ch));
        }

        curl_close($ch);

        $data = json_decode($response, true);

        if (isset($data['data']) && is_array($data['data']) && !empty($data['data'])) {
            return $data['data'][0]['id'];
        }

        return false;
    }

    /**
     * Handle payout error responses
     */
    private function handlePayoutError($errorCode)
    {
        $errorMessages = [
            "34" => "คุณยังเข้ากลุ่มไม่ถึง 14 วัน หรือ โรบัคกลุ่มหมด",
            "1" => "กลุ่มไม่ถูกต้อง กรุณาติดต่อแอดมิน",
            "12" => "โรบัคคงเหลือในกลุ่มไม่เพียงพอ",
            "25" => "โรบัคคงเหลือในกลุ่มไม่เพียงพอ",
            "26" => "ผู้รับโรบัคเยอะเกินไปในขณะนี้",
            "28" => "ขณะนี้มีผู้ใช้ซื้อโรบัคเป็นจำนวนมาก กรุณาลองทำรายการใหม่อีกครั้ง"
        ];

        $message = $errorMessages[$errorCode] ?? "เกิดข้อผิดพลาดที่ไม่รู้จัก $errorCode";

        return [
            "status" => "error",
            "message" => $message
        ];
    }

    /**
     * Main payout function
     */
    public function payout($username, $accountId, $amount, $groupId, $cookie, $secretKey)
    {
        try {
            // Refresh cookie first
            // $cookie = $this->refreshCookie($cookie);

            // Get user ID from username
            $userId = $this->getUserIdByUsername($username);
            if (!$userId) {
                return json_encode([
                    "status" => "error",
                    "message" => "ไม่พบผู้ใช้นี้"
                ]);
            }

            // Check if user is in group
            if (!$this->checkUserInGroup($groupId, $userId)) {
                return json_encode([
                    "status" => "error",
                    "message" => "คุณยังไม่ได้เข้ากลุ่ม"
                ]);
            }

            // Get CSRF token
            $csrfToken = $this->getCSRFToken($cookie);

            // Generate 2FA code
            $twoFactorCode = $this->generateOTP($secretKey);

            // Handle payout process
            [$challengeId, $rblxChallengeId] = $this->handlePayoutChallenge($groupId, $userId, $amount, $cookie, $csrfToken);

            // Verify challenge
            $verificationToken = $this->verifyChallenge($accountId, $challengeId, $twoFactorCode, $csrfToken, $cookie);

            // Prepare challenge metadata
            $challengeMetadata = [
                'verificationToken' => $verificationToken,
                'rememberDevice' => false,
                'challengeId' => $challengeId,
                'actionType' => 'Generic'
            ];

            // Continue challenge
            $this->continueChallenge($challengeId, $rblxChallengeId, $verificationToken, $csrfToken, $cookie);

            // Finalize payout
            $response = $this->finalizePayout($groupId, $userId, $amount, $rblxChallengeId, $challengeMetadata, $csrfToken, $cookie);
            $responseData = json_decode($response, true);

            // Check for errors
            if (isset($responseData['errors']) && !empty($responseData['errors'])) {
                $errorCode = $responseData['errors'][0]['code'];
                return json_encode($this->handlePayoutError($errorCode));
            }

            // Success response
            return json_encode([
                "status" => "success",
                "message" => "เติมโรบัคเสร็จสิ้น หากไม่ได้รับโรบัคให้ติดต่อแอดมิน"
            ]);

        } catch (Exception $e) {
            return json_encode([
                "status" => "error",
                "message" => "เกิดข้อผิดพลาด: " . $e->getMessage()
            ]);
        }
    }
}
