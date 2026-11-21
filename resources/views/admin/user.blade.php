<div>
    <strong>{{ $user->name }}</strong>
    <p>{{ $user->email }}</p>
    <p>{{ __($user->is_super_admin ? 'Суперадминистратор' : ($user->suspended_at ? 'Заблокирован' : 'Активен')) }}</p>
</div>
@unless($user->is_super_admin)
    <form action="{{ route('admin.users.update', $user) }}" method="post">
        @csrf
        @method('PATCH')
        <input type="hidden" name="suspended" value="{{ $user->suspended_at ? 0 : 1 }}">
        <button type="submit" class="button outline small">{{ $user->suspended_at ? __('Разблокировать') : __('Заблокировать') }}</button>
    </form>
@endunless
