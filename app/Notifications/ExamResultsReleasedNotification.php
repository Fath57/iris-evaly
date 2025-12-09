<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamResultsReleasedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Exam $exam;

    /**
     * Create a new notification instance.
     */
    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Résultats disponibles - ' . $this->exam->title)
            ->greeting('Bonjour ' . $notifiable->first_name . ',')
            ->line('Les résultats de l\'examen "' . $this->exam->title . '" sont maintenant disponibles.')
            ->line('Matière : ' . $this->exam->subject->name)
            ->action('Voir mes résultats', url('/student/exams/' . $this->exam->id . '/results'))
            ->line('Merci d\'utiliser notre plateforme !');
    }

    /**
     * Get the array representation of the notification (for database).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'exam_results_released',
            'exam_id' => $this->exam->id,
            'exam_title' => $this->exam->title,
            'subject_name' => $this->exam->subject->name,
            'message' => 'Les résultats de l\'examen "' . $this->exam->title . '" sont disponibles.',
        ];
    }
}
