<td>
    <div class="admin-item">
        <span class="admin-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
        <div><strong>{{ $user->name }}</strong><p>{{ $user->email }}</p></div>
    </div>
</td>
<td>
    <span class="admin-state {{ $user->suspended_at ? 'admin-state-muted' : '' }}">{{ __($user->is_super_admin ? 'Суперадминистратор' : ($user->suspended_at ? 'Заблокирован' : 'Активен')) }}</span>
</td>
<td>
    @unless($user->is_super_admin)
        <form action="{{ route('admin.users.update', $user) }}" method="post">
            @csrf
            @method('PATCH')
            <input type="hidden" name="suspended" value="{{ $user->suspended_at ? 0 : 1 }}">
            <button type="submit" class="button outline small">{{ $user->suspended_at ? __('Разблокировать') : __('Заблокировать') }}</button>
        </form>
    @endunless
</td>
