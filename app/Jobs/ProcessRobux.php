<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\Group;
use App\Models\RobuxTransaction;
use Bavix\Wallet\Models\Transaction;
use App\Services\RobuxService;
use Throwable;

class ProcessRobux implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected $group_id,
        protected $user_id,
        protected $username,
        protected $amount,
        protected $tran_id,
        protected $robux_tran_id
    ) {}


    /**
     * Execute the job.
     */
    public function handle(RobuxService $robuxService)
    {
        $transaction = RobuxTransaction::findOrFail($this->robux_tran_id);
        $moneyTransaction = Transaction::findOrFail($this->tran_id);
        $user = null;


        try {
            if ($transaction->status !== 'pending') {
                logger()->info('Transaction already processed', ['id' => $transaction->id, 'status' => $transaction->status]);
                return;
            }
            $transaction->update(['status' => 'processing']);

            $user = User::findOrFail($this->user_id);
            $group = Group::where('group_id', $this->group_id)->firstOrFail();
            $robloxAccount = $group->robloxUsers->where('user_type','owner')->first();
            logger(json_encode($robloxAccount));
            $payout = $this->processRobuxPayout(
                $robuxService,
                $robloxAccount->roblox_user_id,
                $robloxAccount->cookie,
                $robloxAccount->secret_key
            );

            $this->handlePayoutResult($payout, $transaction, $user, $moneyTransaction);
        } catch (Throwable $e) {
            logger()->error('ProcessRobux failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'group_id' => $this->group_id,
                'user_id' => $this->user_id,
                'transaction_id' => $transaction->id ?? null,
            ]);

            if ($user) {
                $this->handleFailure($transaction, $user, $moneyTransaction, $e->getMessage());
            } else {
                // fallback กรณี $user หาไม่เจอเลย
                $transaction->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }


    /**
     * Process the robux payout through the service.
     */
    private function processRobuxPayout(
        RobuxService $robuxService,
        $robloxUserId,
        $cookie,
        $secretKey
    ) {
        return $robuxService->payout(
            $this->username,
            $robloxUserId,
            $this->amount,
            $this->group_id,
            $cookie,
            $secretKey
        );
    }

    /**
     * Handle the payout result.
     */
    private function handlePayoutResult(
        $payout,
        $transaction,
        $user,
        $moneyTransaction
    ) {
        $payout = json_decode($payout, true); // true = แปลงเป็น array
        logger($payout);
        if (!isset($payout['status'])) {
            $this->handleFailure(
                $transaction,
                $user,
                $moneyTransaction,
                'เกิดข้อผิดพลาดจากเซอร์วิสระบบเติมโรบัค'
            );
            return;
        }

        if ($payout['status'] === 'success') {
            $transaction->update(['status' => 'success']);
        } else {
            $this->handleFailure(
                $transaction,
                $user,
                $moneyTransaction,
                $payout['message'] ?? 'เกิดข้อผิดพลาดที่ไม่รู้จัก'
            );
        }
    }

    /**
     * Handle transaction failure.
     */
    private function handleFailure(
        $transaction,
        $user,
        $moneyTransaction,
        $errorMessage
    ) {
        $transaction->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);

        $user->resetConfirm($moneyTransaction);
    }
}
