<td>
    <div class="admin-item">
        <img class="admin-thumb" src="{{ route('listings.photo', $listing) }}" alt="" loading="lazy" width="52" height="52">
        <div>
            <a class="text-link" href="{{ route('listings.show', $listing) }}">{{ $listing->title }}</a>
            <p>{{ $listing->owner->name }} · {{ $listing->formattedSalePrice() }}</p>
            @if($listing->moderation_reason)<p>{{ $listing->moderation_reason }}</p>@endif
        </div>
    </div>
</td>
<td><span class="admin-state {{ $listing->hidden_at ? 'admin-state-muted' : '' }}">{{ $listing->hidden_at ? __('Скрыто') : __('Опубликовано') }}</span></td>
<td>
    <a class="text-link" href="{{ route('admin.listings.edit', $listing) }}">{{ __('Редактировать') }}</a>
    <form action="{{ route('admin.listings.update', $listing) }}" method="post">
        @csrf
        @method('PATCH')
        <input type="hidden" name="hidden" value="{{ $listing->hidden_at ? 0 : 1 }}">
        @unless($listing->hidden_at)
            <input name="moderation_reason" maxlength="500" aria-label="{{ __('Причина скрытия') }}" placeholder="{{ __('Причина скрытия') }}">
        @endunless
        <button type="submit" class="button outline small">{{ $listing->hidden_at ? __('Восстановить') : __('Скрыть') }}</button>
    </form>
</td>
