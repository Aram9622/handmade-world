<section class="listing-comments" id="comments" aria-labelledby="comments-heading">
    <h2 id="comments-heading">{{ __('Комментарии') }} · {{ $comments->total() }}</h2>

    @auth
        <form class="stack comment-form" method="post" action="{{ route('listings.comment', $listing) }}#comments">
            @csrf
            <label>{{ __('Ваш комментарий') }}
                <textarea name="comment" rows="3" maxlength="1000" required>{{ old('comment') }}</textarea>
            </label>
            @error('comment')
                <p class="notice error" role="alert">{{ $message }}</p>
            @enderror
            <button type="submit" class="button">{{ __('Оставить комментарий') }}</button>
        </form>
    @else
        <a class="text-link" href="{{ route('listings.participate', $listing) }}">{{ __('Войти, чтобы оставить комментарий') }}</a>
    @endauth

    @forelse($comments as $comment)
        <article class="listing-comment">
            <div class="comment-meta">
                <strong>{{ $comment->author->name }}</strong>
                <time datetime="{{ $comment->created_at->toAtomString() }}">{{ $comment->created_at->locale(app()->getLocale())->isoFormat('LL') }}</time>
            </div>
            <p>{{ $comment->body }}</p>
        </article>
    @empty
        <p class="contact">{{ __('Комментариев пока нет.') }}</p>
    @endforelse

    @include('listing-pagination', ['listings' => $comments])
</section>
