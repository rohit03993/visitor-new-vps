<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\InteractionHistory;
use App\Models\Visitor;

class VisitAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $interaction;
    protected $visitor;
    protected $assignedBy;

    /**
     * Create a new notification instance.
     */
    public function __construct(InteractionHistory $interaction, Visitor $visitor, $assignedBy)
    {
        $this->interaction = $interaction;
        $this->visitor = $visitor;
        $this->assignedBy = $assignedBy;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // DISABLED FOR DEBUGGING - This was causing "New Visit Assignment" notifications
        return []; // Empty array = no channels = no notifications
    }

    /**
     * Get the database representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'visit_assigned',
            'title' => 'New Visit Assignment',
            'message' => "You have been assigned a new visit: {$this->visitor->name} - {$this->interaction->purpose}",
            'data' => [
                'interaction_id' => $this->interaction->interaction_id,
                'visitor_id' => $this->visitor->visitor_id,
                'visitor_name' => $this->visitor->name,
                'purpose' => $this->interaction->purpose,
                'assigned_by' => $this->assignedBy,
                'assigned_at' => now()->toISOString(),
            ],
            'timestamp' => now()->toISOString(),
            'user_id' => $notifiable->user_id,
            'user_name' => $notifiable->name,
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toBroadcast(object $notifiable): array
    {
        return [
            'type' => 'visit_assigned',
            'title' => 'New Visit Assignment',
            'message' => "You have been assigned a new visit: {$this->visitor->name} - {$this->interaction->purpose}",
            'data' => [
                'interaction_id' => $this->interaction->interaction_id,
                'visitor_id' => $this->visitor->visitor_id,
                'visitor_name' => $this->visitor->name,
                'purpose' => $this->interaction->purpose,
                'assigned_by' => $this->assignedBy,
                'assigned_at' => now()->toISOString(),
            ],
            'timestamp' => now()->toISOString(),
            'user_id' => $notifiable->user_id,
            'user_name' => $notifiable->name,
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'visit_assigned',
            'title' => 'New Visit Assignment',
            'message' => "You have been assigned a new visit: {$this->visitor->name} - {$this->interaction->purpose}",
            'data' => [
                'interaction_id' => $this->interaction->interaction_id,
                'visitor_id' => $this->visitor->visitor_id,
                'visitor_name' => $this->visitor->name,
                'purpose' => $this->interaction->purpose,
                'assigned_by' => $this->assignedBy,
                'assigned_at' => now()->toISOString(),
            ],
            'timestamp' => now()->toISOString(),
            'user_id' => $notifiable->user_id,
            'user_name' => $notifiable->name,
        ];
    }
}
