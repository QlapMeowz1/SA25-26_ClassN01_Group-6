<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PoolUpdated implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public int $matchId;
    public array $poolData;

    public function __construct(int $matchId, array $poolData)
    {
        $this->matchId = $matchId;
        $this->poolData = $poolData;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('match.' . $this->matchId);
    }
}
