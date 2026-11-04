<a href="{{ route('dashboard.orders.show', $o) }}" class="row-i">
    <span class="avatar">{{ initials($o->ship_name ?? $o->customer?->name) }}</span>
    <span class="main">
        <span class="t">{{ $o->ship_name ?? $o->customer?->name }}</span>
        <span class="meta">
            <span>#{{ $o->number }}</span>
            <span>{{ icon('pin', 13) }} {{ $o->fulfillment === 'pickup' ? 'استلام من الفرع' : ($o->ship_city ?: $o->ship_state) }}</span>
            <span>{{ icon('card', 13) }} {{ \App\Services\Payments\PaymentManager::availableDrivers()[$o->payment_method] ?? $o->payment_method }}</span>
            <span><i class="sdot s-{{ $o->status }}"></i>{{ $o->statusLabel() }}</span>
        </span>
    </span>
    <span class="amt">{{ money($o->total) }}</span>
    <span class="ago">{{ $o->created_at->diffForHumans() }}</span>
</a>
