<?php

namespace App\Actions\Courses;

use App\Models\Courses\Subscription;
use App\Models\Courses\SubscriptionPlan;
use App\Models\User;
use InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class SubscribeUserAction
{
    public function execute(User $user, SubscriptionPlan $plan, array $paymentData = []): Subscription
    {
        // Validate plan is active
        if (!$plan->is_active) {
            throw new InvalidArgumentException('El plan de suscripción no está disponible.');
        }

        // Check for existing active subscription to same plan
        $existingSubscription = Subscription::where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->whereIn('status', [
                Subscription::STATUS_ACTIVE,
                Subscription::STATUS_TRIAL,
                Subscription::STATUS_PAST_DUE,
            ])
            ->first();

        if ($existingSubscription) {
            throw new InvalidArgumentException('Ya tienes una suscripción activa a este plan.');
        }

        return DB::transaction(function () use ($user, $plan, $paymentData) {
            $now = now();
            $startsAt = $now;

            // Calculate end date based on billing cycle
            $endsAt = $this->calculateEndDate($startsAt, $plan->billing_cycle);

            // Determine initial status (trial or active)
            $status = Subscription::STATUS_ACTIVE;
            $trialEndsAt = null;

            if ($plan->trial_days > 0) {
                $status = Subscription::STATUS_TRIAL;
                $trialEndsAt = $now->copy()->addDays($plan->trial_days);
            }

            $subscription = Subscription::create([
                'tenant_id' => $plan->tenant_id,
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => $status,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'trial_ends_at' => $trialEndsAt,
                'cancelled_at' => null,
                'payment_provider' => $paymentData['provider'] ?? null,
                'payment_provider_id' => $paymentData['provider_id'] ?? null,
                'payment_method' => $paymentData['method'] ?? null,
                'last_payment_at' => empty($paymentData) ? null : $now,
                'next_payment_at' => $endsAt,
                'amount' => $plan->price,
                'currency' => $plan->currency,
            ]);

            return $subscription;
        });
    }

    public function renew(Subscription $subscription): Subscription
    {
        if (!$subscription->canRenew()) {
            throw new InvalidArgumentException('Esta suscripción no puede ser renovada.');
        }

        $plan = $subscription->plan;
        $newEndsAt = $this->calculateEndDate($subscription->ends_at, $plan->billing_cycle);

        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'ends_at' => $newEndsAt,
            'next_payment_at' => $newEndsAt,
            'last_payment_at' => now(),
        ]);

        return $subscription->fresh();
    }

    public function changePlan(Subscription $subscription, SubscriptionPlan $newPlan): Subscription
    {
        if (!$newPlan->is_active) {
            throw new InvalidArgumentException('El nuevo plan no está disponible.');
        }

        // Calculate prorated amount or new billing cycle
        $subscription->update([
            'plan_id' => $newPlan->id,
            'amount' => $newPlan->price,
            'currency' => $newPlan->currency,
        ]);

        return $subscription->fresh();
    }

    private function calculateEndDate($startDate, string $billingCycle): \Carbon\Carbon
    {
        $date = $startDate instanceof \Carbon\Carbon ? $startDate->copy() : now();

        return match ($billingCycle) {
            SubscriptionPlan::BILLING_WEEKLY => $date->addWeek(),
            SubscriptionPlan::BILLING_MONTHLY => $date->addMonth(),
            SubscriptionPlan::BILLING_QUARTERLY => $date->addMonths(3),
            SubscriptionPlan::BILLING_BIANNUAL => $date->addMonths(6),
            SubscriptionPlan::BILLING_YEARLY => $date->addYear(),
            SubscriptionPlan::BILLING_LIFETIME => $date->addYears(100),
            default => $date->addMonth(),
        };
    }
}
