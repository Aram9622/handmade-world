<td>
    <a class="text-link" href="{{ route('listings.show', $comment->listing) }}#comments">{{ $comment->listing->title }}</a>
    <p><strong>{{ $comment->author->name }}</strong></p>
    <p class="description">{{ $comment->body }}</p>
</td>
<td><span class="admin-state {{ $comment->hidden_at ? 'admin-state-muted' : '' }}">{{ $comment->hidden_at ? __('Скрыто') : __('Опубликовано') }}</span></td>
<td>
    <form action="{{ route('admin.comments.update', $comment) }}" method="post">
        @csrf
        @method('PATCH')
        <input type="hidden" name="hidden" value="{{ $comment->hidden_at ? 0 : 1 }}">
        <button type="submit" class="button outline small">{{ $comment->hidden_at ? __('Восстановить') : __('Скрыть') }}</button>
    </form>
</td>
