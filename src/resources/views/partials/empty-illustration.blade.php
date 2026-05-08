<div class="empty-illustration" aria-hidden="true" style="display:flex;align-items:center;gap:16px;padding:16px;">
    <svg width="72" height="72" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <rect x="2" y="6" width="20" height="12" rx="2" fill="#f3f4f6" stroke="#e5e7eb" />
        <path d="M7 10h10" stroke="#d1d5db" stroke-width="1.2" stroke-linecap="round"/>
        <path d="M7 13h6" stroke="#d1d5db" stroke-width="1.2" stroke-linecap="round"/>
    </svg>
    <div>
        <strong>{{ $title ?? 'Nothing here yet' }}</strong>
        <div class="muted small">{{ $message ?? 'Try adjusting filters, or create an item to get started.' }}</div>
    </div>
</div>
