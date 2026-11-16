@extends('layouts.site')

@section('title', __('My Call History - VoxPulse PBX'))
@section('body-class', 'account-body')

@section('content')
<div class="account-shell">
    @if(auth('web')->check() || auth('admin')->check())
        @include('user.partials.account_nav', ['user' => auth('web')->user() ?: auth('admin')->user()])
    @endif

    <section class="account-hero">
        <div>
            <p class="eyebrow">{{ __('Internal Communications') }}</p>
            <h1 class="account-title">{{ __('My Call History') }}</h1>
            <p class="account-sub">{{ __('Review your incoming, outgoing, and internal call records with extension duration.') }}</p>
        </div>
        <div class="hero-card highlight-card">
            <div class="card-meta">{{ __('My Extension') }}</div>
            <div class="card-value">{{ $ext ? $ext->extension : __('None') }}</div>
            <p class="card-sub">
                @if($ext)
                    <span class="text-success fw-bold d-inline-flex align-items-center gap-1">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg>
                        {{ $ext->name }}
                    </span>
                @else
                    <span class="text-muted">{{ __('No extension assigned') }}</span>
                @endif
            </p>
        </div>
    </section>

    <div class="account-card" style="padding: 24px; margin-top: 24px; border-radius: 12px; background: #ffffff; border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #0f172a;">{{ __('Recent Call Activity') }}</h3>
                <span style="display: inline-block; background: #f1f5f9; color: #475569; padding: 2px 8px; border-radius: 9999px; font-size: 12px; font-weight: 600;">
                    {{ $logs->total() }} {{ __('Records') }}
                </span>
            </div>

            <button type="button" class="btn btn-primary btn-sm" onclick="if (typeof togglePbxDialer === 'function') togglePbxDialer();" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                <span>{{ __('Open Dialer') }}</span>
            </button>
        </div>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0; color: #64748b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 10px 14px;">{{ __('Caller / Recipient') }}</th>
                        <th style="padding: 10px 14px;">{{ __('Direction') }}</th>
                        <th style="padding: 10px 14px;">{{ __('Duration') }}</th>
                        <th style="padding: 10px 14px;">{{ __('Status') }}</th>
                        <th style="padding: 10px 14px;">{{ __('Date & Time') }}</th>
                        <th style="padding: 10px 14px; text-align: right;">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        @php
                            $isOutgoing = $ext && $log->caller === $ext->extension;
                            $otherParty = $isOutgoing ? ($log->callee_name ?: ('Ext ' . $log->callee)) : ($log->caller_name ?: ('Ext ' . $log->caller));
                            $otherNumber = $isOutgoing ? $log->callee : $log->caller;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 14px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #0f172a; font-size: 13px;">
                                        {{ substr($otherNumber, 0, 2) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 600; color: #0f172a;">{{ $otherParty }}</div>
                                        <div style="color: #64748b; font-size: 12px;">Ext: {{ $otherNumber }}</div>
                                    </div>
                                </div>
                            </td>

                            <td style="padding: 14px;">
                                @if($isOutgoing)
                                    <span style="display: inline-flex; align-items: center; gap: 4px; color: #2563eb; background: #eff6ff; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                                        {{ __('Outgoing') }}
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 4px; color: #0891b2; background: #ecfeff; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="17" y1="7" x2="7" y2="17"/><polyline points="17 17 7 17 7 7"/></svg>
                                        {{ __('Incoming') }}
                                    </span>
                                @endif
                            </td>

                            <td style="padding: 14px; font-family: monospace; font-weight: 600; color: #0f172a;">
                                {{ $log->formatted_duration }}
                            </td>

                            <td style="padding: 14px;">
                                @if($log->status === 'answered')
                                    <span style="display: inline-flex; align-items: center; gap: 4px; color: #16a34a; background: #f0fdf4; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        {{ __('Answered') }}
                                    </span>
                                @elseif(in_array($log->status, ['missed', 'no-answer']))
                                    <span style="display: inline-flex; align-items: center; gap: 4px; color: #dc2626; background: #fef2f2; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        {{ __('Missed') }}
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; gap: 4px; color: #64748b; background: #f8fafc; padding: 3px 8px; border-radius: 6px; font-size: 12px; font-weight: 600;">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                @endif
                            </td>

                            <td style="padding: 14px; color: #64748b; font-size: 13px;">
                                <div>{{ $log->created_at->format('M d, Y') }}</div>
                                <div style="font-size: 11px; color: #94a3b8;">{{ $log->created_at->format('h:i A') }}</div>
                            </td>

                            <td style="padding: 14px; text-align: right;">
                                <button type="button" class="btn btn-secondary btn-sm" onclick="if (typeof triggerPbxCall === 'function') triggerPbxCall('{{ $otherNumber }}');" style="display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; font-size: 12px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <span>{{ __('Call') }}</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px 14px; color: #64748b;">
                                <div style="font-size: 15px; font-weight: 600; color: #0f172a; margin-bottom: 4px;">{{ __('No calls recorded yet') }}</div>
                                <p style="margin: 0; font-size: 13px;">{{ __('Your internal and external calls will appear here automatically.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div style="margin-top: 20px;">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
