<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use Filament\Notifications\Notification;

class PlanLimitChecker
{
    /**
     * @param string $featureKey مثال: MAX_MENU_ITEMS, MAX_ANALYZED_MENU_ITEMS
     * @param \Closure $countCallback دالة بترجع العدد الحالي (يُنفذ جوا الـ tenant context)
     * @param string $limitReachedTitle
     * @param string $limitReachedBodyTemplate استخدم {limit} كـ placeholder
     */
    public static function check(
        string $featureKey,
        \Closure $countCallback,
        string $limitReachedTitle = 'تم الوصول للحد الأقصى',
        string $limitReachedBodyTemplate = 'خطتك تسمح بـ {limit} عناصر فقط.',
    ): bool {
        $user = auth()->user();

        if (! $user || ! tenancy()->initialized) {
            return false;
        }

        $tenant = tenant();

        if (! $tenant || $tenant->owner_id !== $user->id) {
            return false;
        }

        $subscription = Subscription::where('tenant_id', $tenant->id)->first();

        if (! $subscription) {
            Notification::make()
                ->title('لا يوجد اشتراك فعّال')
                ->body('يرجى الاشتراك في إحدى الخطط للمتابعة.')
                ->danger()
                ->send();

            return false;
        }

        $plan = Plan::find($subscription->plan_id);

        if (! $plan) {
            return false;
        }

        $limit = $plan->featureValue($featureKey);

        // Unlimited (وحّد القيمة: null أو -1، مو الاثنين مختلفين ببعض الأماكن)
        if ($limit === null || $limit === -1) {
            return true;
        }

        $count = $countCallback();

        if ($count >= $limit) {
            Notification::make()
                ->title($limitReachedTitle)
                ->body(str_replace('{limit}', (string) $limit, $limitReachedBodyTemplate))
                ->danger()
                ->send();

            return false;
        }

        return true;
    }
}