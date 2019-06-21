<h2>{{ $concert->title }}</h2>
<h2>{{ $concert->subtitle }}</h2>
<h2>{{ $concert->date->format('F j, Y') }}</h2>
<p>Doors at {{ $concert->date->format('g:ia') }}</p>
<p>{{ number_format($concert->ticket_price / 100, 2) }}</p>
<p>{{ $concert->venue }}</p>
<p>{{ $concert->venue_address }}</p>
<p>
    {{ $concert->city }}, {{ $concert->state }} {{$concert->zip}}
</p>
<p>
    {{ $concert->additional_information }}
</p>