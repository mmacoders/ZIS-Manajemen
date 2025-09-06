<?php

namespace App\Events;

use App\Models\Distribution;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DistributionCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $distribution;

    /**
     * Create a new event instance.
     */
    public function __construct(Distribution $distribution)
    {
        $this->distribution = $distribution->load(['program', 'mustahiq']);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('distributions'),
            new PrivateChannel('admin-notifications'),
            new PrivateChannel('bidang2-notifications'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->distribution->id,
            'nomor_distribusi' => $this->distribution->nomor_distribusi,
            'program_nama' => $this->distribution->program->nama ?? 'Unknown Program',
            'mustahiq_nama' => $this->distribution->mustahiq->nama ?? 'Unknown Mustahiq',
            'jumlah' => $this->distribution->jumlah,
            'status' => $this->distribution->status,
            'tanggal_distribusi' => $this->distribution->tanggal_distribusi,
            'message' => "Distribusi {$this->distribution->program->nama} kepada {$this->distribution->mustahiq->nama} sebesar Rp " . number_format($this->distribution->jumlah, 0, ',', '.') . " telah selesai",
            'type' => 'distribution_completed',
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Get the event name for broadcasting.
     */
    public function broadcastAs(): string
    {
        return 'distribution.completed';
    }
}