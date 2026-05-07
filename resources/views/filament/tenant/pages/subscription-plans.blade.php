{{-- resources/views/filament/tenant/pages/subscription-plans.blade.php --}}
<x-filament-panels::page>

    {{-- ── Post-submit success banner ── --}}
    @if ($this->justSubmitted)
        <div style="
            display:flex;align-items:center;gap:10px;
            background:#f0fdf4;
            border:1px solid #86efac;
            border-radius:10px;
            padding:14px 18px;
        ">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                 xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                      stroke="#16a34a" stroke-width="2"
                      stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div>
                <p style="font-size:13px;font-weight:500;color:#15803d;margin:0;">
                    Request submitted — {{ $this->submittedPlanName }}
                </p>
                <p style="font-size:12px;color:#16a34a;margin:2px 0 0;">
                    The team will activate your plan within 2 hours at most.
                </p>
            </div>
        </div>
    @endif

    {{-- ── Active subscription banner ── --}}
    @if ($this->currentSubscription)
        @php $sub = $this->currentSubscription; @endphp
        <div style="
            display:flex;align-items:center;justify-content:space-between;
            flex-wrap:wrap;gap:12px;
            background:#fff;
            border:1px solid #e5e7eb;
            border-left:4px solid #22c55e;
            border-radius:10px;
            padding:14px 18px;
        ">
            <div style="display:flex;align-items:center;gap:10px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                     xmlns="http://www.w3.org/2000/svg" style="flex-shrink:0;">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                          stroke="#16a34a" stroke-width="2"
                          stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div>
                    <p style="font-size:13px;font-weight:500;color:#111827;margin:0;">
                        Currently on the
                        <span style="color:#16a34a;">{{ $sub->plan->name }}</span>
                        plan
                        @if ($sub->status === 'trial')
                            &mdash; Trial
                        @endif
                    </p>
                    @if ($sub->ends_at)
                        <p style="font-size:12px;color:#9ca3af;margin:2px 0 0;">
                            Expires {{ $sub->ends_at->format('d M Y') }}
                            &middot; {{ $sub->daysRemaining() }} days remaining
                        </p>
                    @elseif ($sub->trial_ends_at)
                        <p style="font-size:12px;color:#9ca3af;margin:2px 0 0;">
                            Trial ends {{ $sub->trial_ends_at->format('d M Y') }}
                            &middot; {{ $sub->daysRemaining() }} days remaining
                        </p>
                    @endif
                </div>
            </div>
            <span style="
                font-size:11px;font-weight:500;
                padding:3px 10px;border-radius:6px;
                background:#dcfce7;color:#15803d;
                border:1px solid #86efac;
            ">
                {{ $sub->status === 'trial' ? 'Trial' : 'Active' }}
            </span>
        </div>
    @endif

    {{-- ── Plans grid ── --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

        @foreach ($this->plans as $plan)
            @php
                $isCurrentPlan = $this->currentSubscription?->plan_id === $plan->id;
                $isFree        = $plan->price == 0;
                $isPro         = $plan->code === 'PRO';
            @endphp

            <div style="
                background:#fff;
                border-radius:12px;
                padding:20px;
                display:flex;
                flex-direction:column;
                position:relative;
                margin-top:10px;
                {{ $isCurrentPlan
                    ? 'border:2px solid #22c55e;'
                    : ($isPro
                        ? 'border:2px solid #6366f1;'
                        : 'border:1px solid #e5e7eb;') }}
            ">

                {{-- ── Top badge ── --}}
                @if ($isCurrentPlan)
                    <div style="position:absolute;top:-13px;left:50%;transform:translateX(-50%);white-space:nowrap;">
                        <span style="
                            display:inline-flex;align-items:center;gap:4px;
                            font-size:11px;font-weight:500;
                            padding:3px 10px;border-radius:6px;
                            background:#dcfce7;color:#15803d;
                            border:1px solid #86efac;
                        ">
                            ✓ Your current plan
                        </span>
                    </div>
                @elseif ($isPro)
                    <div style="position:absolute;top:-13px;left:50%;transform:translateX(-50%);white-space:nowrap;">
                        <span style="
                            display:inline-flex;align-items:center;gap:4px;
                            font-size:11px;font-weight:500;
                            padding:3px 10px;border-radius:6px;
                            background:#ede9fe;color:#6d28d9;
                            border:1px solid #c4b5fd;
                        ">
                            ⭐ Most popular
                        </span>
                    </div>
                @endif

                {{-- ── Plan name ── --}}
                <p style="
                    font-size:11px;font-weight:600;
                    letter-spacing:.07em;text-transform:uppercase;
                    margin:0 0 10px;
                    {{ $isCurrentPlan ? 'color:#16a34a;' : ($isPro ? 'color:#6366f1;' : 'color:#9ca3af;') }}
                ">
                    {{ $plan->name }}
                </p>

                {{-- ── Price ── --}}
                <div style="display:flex;align-items:baseline;gap:4px;margin-bottom:6px;">
                    @if ($isFree)
                        <span style="font-size:26px;font-weight:600;color:#111827;">
                            Free
                        </span>
                    @else
                        <span style="font-size:26px;font-weight:600;color:#111827;">
                            ${{ number_format($plan->price, 0) }}
                        </span>
                        <span style="font-size:13px;color:#9ca3af;">
                            / {{ $plan->billing_interval }}
                        </span>
                    @endif
                </div>

                {{-- ── Interval badge ── --}}
                <div style="margin-bottom:14px;">
                    @php
                        $intervalStyle = match($plan->billing_interval) {
                            'yearly'   => 'background:#dcfce7;color:#15803d;border:1px solid #86efac;',
                            'lifetime' => 'background:#fef3c7;color:#92400e;border:1px solid #fcd34d;',
                            default    => 'background:#dbeafe;color:#1d4ed8;border:1px solid #93c5fd;',
                        };
                    @endphp
                    <span style="
                        display:inline-block;
                        font-size:11px;font-weight:500;
                        padding:2px 8px;border-radius:6px;
                        {{ $intervalStyle }}
                    ">
                        {{ ucfirst($plan->billing_interval) }}
                    </span>
                </div>

                {{-- ── Divider ── --}}
                <div style="border-top:1px solid #f3f4f6;margin-bottom:14px;"></div>

                {{-- ── Features ── --}}
                <ul style="
                    list-style:none;padding:0;
                    margin:0 0 18px;
                    display:flex;flex-direction:column;gap:9px;
                    flex:1;
                ">
                    @foreach ($plan->features as $feature)
                        @php
                            $val     = $feature->pivot->value;
                            $type    = $feature->type;
                            $enabled = match($type) {
                                'boolean' => $val === 'true',
                                'limit'   => $val !== '0',
                                default   => true,
                            };
                            $label = match(true) {
                                $type === 'limit' && $val === '-1'
                                    => 'Unlimited ' . strtolower($feature->name),
                                $type === 'limit'
                                    => $val . ' ' . strtolower($feature->name),
                                default => $feature->name,
                            };
                        @endphp

                        <li style="
                            display:flex;align-items:center;gap:8px;
                            font-size:13px;
                            {{ $enabled ? 'color:#374151;' : 'color:#d1d5db;' }}
                        ">
                            @if ($enabled)
                                <svg width="15" height="15" viewBox="0 0 20 20"
                                     fill="none" xmlns="http://www.w3.org/2000/svg"
                                     style="flex-shrink:0;">
                                    <circle cx="10" cy="10" r="10"
                                            fill="{{ $isCurrentPlan ? '#22c55e' : '#6366f1' }}"
                                            fill-opacity=".15"/>
                                    <path d="M6 10l3 3 5-5"
                                          stroke="{{ $isCurrentPlan ? '#16a34a' : '#6366f1' }}"
                                          stroke-width="1.8"
                                          stroke-linecap="round"
                                          stroke-linejoin="round"/>
                                </svg>
                            @else
                                <svg width="15" height="15" viewBox="0 0 20 20"
                                     fill="none" xmlns="http://www.w3.org/2000/svg"
                                     style="flex-shrink:0;">
                                    <circle cx="10" cy="10" r="10" fill="#e5e7eb"/>
                                    <path d="M7 10h6" stroke="#9ca3af"
                                          stroke-width="1.8"
                                          stroke-linecap="round"/>
                                </svg>
                            @endif
                            {{ $label }}
                        </li>
                    @endforeach
                </ul>

                {{-- ── Action button ── --}}
                @if ($isCurrentPlan)
                    <button disabled style="
                        width:100%;padding:9px;
                        font-size:13px;font-weight:500;
                        border-radius:8px;cursor:not-allowed;
                        background:#dcfce7;color:#15803d;
                        border:1px solid #86efac;
                    ">
                        ✓ Current plan
                    </button>
                @elseif ($isFree)
                    <button disabled style="
                        width:100%;padding:9px;
                        font-size:13px;font-weight:500;
                        border-radius:8px;cursor:not-allowed;
                        background:#f3f4f6;color:#9ca3af;
                        border:1px solid #e5e7eb;
                    ">
                        Free plan
                    </button>
                @else
                    <button
                        wire:click="subscribe({{ $plan->id }})"
                        style="
                            width:100%;padding:9px;
                            font-size:13px;font-weight:500;
                            border-radius:8px;cursor:pointer;
                            {{ $isPro
                                ? 'background:#6366f1;color:#fff;border:1px solid #6366f1;'
                                : 'background:#fff;color:#374151;border:1px solid #d1d5db;' }}
                        "
                    >
                        Subscribe to {{ $plan->name }} →
                    </button>
                @endif

            </div>
        @endforeach

    </div>

</x-filament-panels::page>