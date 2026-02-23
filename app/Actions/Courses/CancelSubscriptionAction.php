<?php

namespace App\Actions\Courses;

use App\Models\Courses\Subscription;
use InvalidArgumentException;

class CancelSubscriptionAction
{
    public function execute(Subscription $subscription, bool $immediately = false): Subscription
    {
        if ($subscription->status === Subscription::STATUS_CANCELLED) {
            throw new InvalidArgumentException('La suscripción ya está cancelada.');
        }

        if ($immediately) {
            // Cancel immediately
            $subscription->update([
                'status' => Subscription::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'ends_at' => now(),
            ]);

            // Expire related enrollments
            $this->expireEnrollments($subscription);
        } else {
            // Cancel at end of billing period
            $subscription->update([
                'cancelled_at' => now(),
                // Status remains active until ends_at
            ]);
        }

        return $subscription->fresh();
    }

    public function reactivate(Subscription $subscription): Subscription
    {
        if ($subscription->status !== Subscription::STATUS_CANCELLED) {
            throw new InvalidArgumentException('Solo se pueden reactivar suscripciones canceladas.');
        }

        // Check if subscription has not expired yet
        if ($subscription->ends_at && $subscription->ends_at->isPast()) {
            throw new InvalidArgumentException('No se puede reactivar una suscripción expirada. Por favor, crea una nueva suscripción.');
        }

        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'cancelled_at' => null,
        ]);

        return $subscription->fresh();
    }

    public function pause(Subscription $subscription): Subscription
    {
        if (!$subscription->isActive()) {
            throw new InvalidArgumentException('Solo se pueden pausar suscripciones activas.');
        }

        $subscription->update([
            'status' => Subscription::STATUS_PAUSED,
        ]);

        return $subscription->fresh();
    }

    public function resume(Subscription $subscription): Subscription
    {
        if ($subscription->status !== Subscription::STATUS_PAUSED) {
            throw new InvalidArgumentException('Solo se pueden reanudar suscripciones pausadas.');
        }

        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
        ]);

        return $subscription->fresh();
    }

    private function expireEnrollments(Subscription $subscription): void
    {
        $subscription->enrollments()->update([
            'status' => \App\Models\Courses\CourseEnrollment::STATUS_EXPIRED,
        ]);
    }
}
