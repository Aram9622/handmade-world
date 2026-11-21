<div>
    <a class="text-link" href="{{ route('listings.show', $listing) }}">{{ $listing->title }}</a>
    <p>{{ $listing->owner->name }} · {{ $listing->formattedSalePrice() }}</p>
    <span class="admin-state {{ $listing->hidden_at ? 'admin-state-muted' : '' }}">{{ $listing->hidden_at ? __('Скрыто') : __('Опубликовано') }}</span>
    @if($listing->moderation_reason)<p>{{ $listing->moderation_reason }}</p>@endif
</div>
<form action="{{ route('admin.listings.update', $listing) }}" method="post">
    @csrf
    @method('PATCH')
    <input type="hidden" name="hidden" value="{{ $listing->hidden_at ? 0 : 1 }}">
    @unless($listing->hidden_at)
        <label>{{ __('Причина скрытия') }}<input name="moderation_reason" maxlength="500"></label>
    @endunless
    <button type="submit" class="button outline small">{{ $listing->hidden_at ? __('Восстановить') : __('Скрыть') }}</button>
</form>
