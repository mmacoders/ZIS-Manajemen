<?php

namespace App\Events;

use App\Models\ZisTransaction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ZisTransactionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $transaction;

    /**
     * Create a new event instance.
     */
    public function __construct(ZisTransaction $transaction)
    {
        $this->transaction = $transaction->load(['muzakki', 'upz']);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('zis-transactions'),
            new PrivateChannel('admin-notifications'),
            new PrivateChannel('bidang1-notifications'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->transaction->id,
            'nomor_transaksi' => $this->transaction->nomor_transaksi,
            'muzakki_nama' => $this->transaction->muzakki->nama ?? 'Unknown',
            'jenis_zis' => $this->transaction->jenis_zis,
            'jumlah' => $this->transaction->jumlah,
            'status' => $this->transaction->status,
            'tanggal_transaksi' => $this->transaction->tanggal_transaksi,
            'message' => "Transaksi ZIS baru dari {$this->transaction->muzakki->nama} sebesar Rp " . number_format($this->transaction->jumlah, 0, ',', '.'),
            'type' => 'new_transaction',
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get the event name for broadcasting.
     */
    public function broadcastAs(): string
    {
        return 'transaction.created';
    }
}