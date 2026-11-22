@foreach($storefrontMenu->get('main', []) as $item)
    <a href="{{ $item['href'] }}">{{ $item['text'] }}</a>
@endforeach
