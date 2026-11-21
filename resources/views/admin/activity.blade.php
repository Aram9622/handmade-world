@php
    $counts = array_column($activity, 'count');
    $maximum = max(1, ...$counts);
@endphp
<section class="admin-panel activity-panel activity-{{ $section }}" aria-labelledby="activity-heading">
    <header class="admin-panel-header">
        <div>
            <h2 id="activity-heading">{{ __('Активность за 7 дней') }}</h2>
            <p>{{ __(match($section) {
                'listings' => 'Новые объявления',
                'comments' => 'Новые комментарии',
                'users' => 'Новые регистрации',
            }) }}</p>
        </div>
        <strong>{{ __('Всего за период: :count', ['count' => array_sum($counts)]) }}</strong>
    </header>
    <ol class="activity-days">
        @foreach($activity as $entry)
            <li>
                <strong>{{ $entry['count'] }}</strong>
                <div class="activity-track" aria-hidden="true">
                    <div class="activity-bar" style="--bar-height: {{ round($entry['count'] * 100 / $maximum) }}%"></div>
                </div>
                <time datetime="{{ $entry['day']->toDateString() }}">{{ $entry['day']->locale(app()->getLocale())->isoFormat('D MMM') }}</time>
            </li>
        @endforeach
    </ol>
</section>
